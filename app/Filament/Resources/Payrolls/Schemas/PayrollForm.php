<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\Contract;
use App\Models\HourBank;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes do Processamento')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Funcionário')
                            ->relationship('employee', 'first_name')
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::loadData($set, $get)),

                        TextInput::make('month_year')
                            ->label('Mês de Referência')
                            ->placeholder('2026-04')
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                            ->default(now()->format('Y-m'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::loadData($set, $get)),

                        Select::make('status')
                            ->label('Status do Pagamento')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(3),

                Section::make('Resumo Financeiro')
                    ->schema([
                        TextInput::make('base_salary')
                            ->label('Salário Bruto')
                            ->numeric()
                            ->prefix('€')
                            ->readOnly()
                            ->dehydrated(),

                        TextInput::make('hourly_rate')
                            ->label('Valor da Hora')
                            ->numeric()
                            ->prefix('€')
                            ->readOnly()
                            ->dehydrated(),

                        TextInput::make('extra_hours')
                            ->label('Saldo de Horas (min)')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated()
                            ->hint('Positivo ou Negativo'),

                        TextInput::make('extra_hours_amount')
                            ->label('Ajuste do Banco de Horas')
                            ->numeric()
                            ->prefix('€')
                            ->readOnly()
                            ->dehydrated()
                            ->default(0)
                            ->dehydrateStateUsing(function (Get $get) {
                                // Force recalculation before saving
                                $employeeId = $get('employee_id');
                                $monthYear = $get('month_year');
                                
                                if (!$employeeId || !$monthYear) {
                                    return 0;
                                }
                                
                                $contract = Contract::where('employee_id', $employeeId)
                                    ->where('status', 'active')
                                    ->first();
                                
                                if (!$contract) {
                                    return 0;
                                }
                                
                                $baseSalary = (float) $contract->salary;
                                $dailyWorkMinutes = (int) ($contract->daily_work_minutes ?? (8 * 60));
                                $hourlyRate = $baseSalary / (($dailyWorkMinutes / 60) * 22);
                                
                                $hourBank = HourBank::where('employee_id', $employeeId)
                                    ->where('month_year', $monthYear)
                                    ->first();
                                
                                $balance = (int) ($hourBank?->balance ?? 0);
                                
                                if ($balance > 0) {
                                    $extraHoursAmount = ($hourlyRate * 1.5) * ($balance / 60);
                                } else {
                                    $extraHoursAmount = ($hourlyRate * 1.0) * ($balance / 60);
                                }
                                
                                return round($extraHoursAmount, 2);
                            })
                            ->formatStateUsing(function (Get $get) {
                                $value = (float) ($get('extra_hours_amount') ?? 0);
                                return round($value, 2);
                            })
                            // Feedback visual dinâmico com base no sinal do valor
                            ->hint(function (Get $get) {
                                $amount = (float) ($get('extra_hours_amount') ?? 0);
                                if ($amount > 0) return '✅ Acréscimo (Horas Extras)';
                                if ($amount < 0) return '❌ Desconto (Horas em Falta)';
                                return 'Sem movimento';
                            })
                            ->hintColor(function (Get $get) {
                                $amount = (float) ($get('extra_hours_amount') ?? 0);
                                if ($amount > 0) return 'success';
                                if ($amount < 0) return 'danger';
                                return 'gray';
                            }),

                        TextInput::make('deductions')
                            ->label('Outras Deduções')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTotal($set, $get)),

                        TextInput::make('total_net')
                            ->label('Salário Líquido')
                            ->numeric()
                            ->prefix('€')
                            ->readOnly()
                            ->dehydrated()
                            ->extraInputAttributes(['class' => 'font-bold text-primary-600']),
                    ])->columns(3),
            ]);
    }

    private static function loadData(Set $set, Get $get): void
    {
        $employeeId = $get('employee_id');
        $monthYear = $get('month_year');

        if (! $employeeId || ! $monthYear) {
            self::resetFinances($set);
            return;
        }

        // Carregar contrato ativo
        $contract = Contract::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->first();

        if (! $contract) {
            self::resetFinances($set);
            return;
        }

        // Cálculo do valor da hora base
        $baseSalary = (float) $contract->salary;
        $dailyWorkMinutes = (int) ($contract->daily_work_minutes ?? (8 * 60));
        $hourlyRate = $baseSalary / (($dailyWorkMinutes / 60) * 22);

        $set('base_salary', round($baseSalary, 2));
        $set('hourly_rate', round($hourlyRate, 2));

        // Carregar banco de horas
        $hourBank = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $monthYear)
            ->first();

        // Usar 'balance' que é o saldo TOTAL já consolidado
        $balance = (int) ($hourBank?->balance ?? 0);
        $set('extra_hours', $balance);

        // Calcular impacto financeiro:
        // - Saldo positivo (+) pago com bónus 1.5x (horas ganhas)
        // - Saldo negativo (-) desconto com taxa 1.0x (horas devidas)
        if ($balance > 0) {
            $extraHoursAmount = ($hourlyRate * 1.5) * ($balance / 60);
        } else {
            // Balance negativo: horas devidas, desconto simples
            // Fórmula: balance é negativo, então (*1.0) dá negativo (desconto)
            $extraHoursAmount = ($hourlyRate * 1.0) * ($balance / 60);
        }

        $set('extra_hours_amount', round($extraHoursAmount, 2));

        self::calculateTotal($set, $get);
    }

    private static function calculateTotal(Set $set, Get $get): void
    {
        $baseSalary = (float) ($get('base_salary') ?? 0);
        // extra_hours_amount já será negativo se o saldo for devedor, então a soma natural fará a subtração do bruto
        $extraHoursAmount = (float) ($get('extra_hours_amount') ?? 0);
        $deductions = (float) ($get('deductions') ?? 0);

        $total = $baseSalary + $extraHoursAmount - $deductions;
        $set('total_net', round(max(0, $total), 2));
    }

    private static function resetFinances(Set $set): void
    {
        $set('base_salary', 0);
        $set('hourly_rate', 0);
        $set('extra_hours', 0);
        $set('extra_hours_amount', 0);
        $set('total_net', 0);
    }
}
