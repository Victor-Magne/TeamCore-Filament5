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
                Section::make('Informações Básicas')
                    ->schema([
                        Select::make('employee_id')
                            ->relationship('employee', 'first_name')
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::recalculatePayroll($set, $get);
                            }),
                        TextInput::make('month_year')
                            ->label('Mês de Referência')
                            ->placeholder('YYYY-MM')
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                            ->validationMessages([
                                'regex' => 'O formato deve ser YYYY-MM (ex: 2024-01).',
                            ])
                            ->default(now()->format('Y-m'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::recalculatePayroll($set, $get);
                            }),
                    ])->columns(2),
                Section::make('Salário e Horas')
                    ->schema([
                        TextInput::make('base_salary')
                            ->label('Salário Bruto')
                            ->numeric()
                            ->prefix('€')
                            ->required()
                            ->dehydrated()
                            ->disabled(),
                        TextInput::make('hourly_rate')
                            ->label('Valor da Hora (Normal)')
                            ->numeric()
                            ->prefix('€')
                            ->dehydrated()
                            ->disabled(),
                        TextInput::make('hourly_rate_premium')
                            ->label('Valor da Hora (Extra × 1.5)')
                            ->numeric()
                            ->prefix('€')
                            ->dehydrated()
                            ->disabled(),
                    ])->columns(3),
                Section::make('Banco de Horas')
                    ->schema([
                        TextInput::make('extra_hours_display')
                            ->label('Horas Extra Disponíveis')
                            ->numeric()
                            ->prefix('h ')
                            ->dehydrated(false)
                            ->disabled()
                            ->hint('Horas extra acumuladas do funcionário'),
                        TextInput::make('extra_hours_amount')
                            ->label('Valor do Bonus de Horas Extra')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->dehydrated()
                            ->disabled(),
                        TextInput::make('balance_display')
                            ->label('Saldo Atual')
                            ->numeric()
                            ->prefix('h ')
                            ->dehydrated(false)
                            ->disabled(),
                    ])->columns(3),
                Section::make('Deduções e Total')
                    ->schema([
                        TextInput::make('deductions')
                            ->label('Deduções (€)')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateTotalNet($set, $get);
                            }),
                        TextInput::make('total_net')
                            ->label('Salário Líquido (Bruto + Extras - Deduções)')
                            ->numeric()
                            ->prefix('€')
                            ->dehydrated()
                            ->disabled(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(2),
            ]);
    }

    private static function recalculatePayroll(Set $set, Get $get): void
    {
        $employeeId = $get('employee_id');
        $monthYear = $get('month_year');

        if (! $employeeId || ! $monthYear) {
            return;
        }

        // 1. Carregar contrato ativo
        $contract = Contract::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->first();

        if (! $contract) {
            return;
        }

        $baseSalary = (float) $contract->salary;
        $dailyWorkMinutes = (int) ($contract->daily_work_minutes ?? (8 * 60));
        $dailyWorkHours = $dailyWorkMinutes / 60;
        $workingDaysPerMonth = 22;

        // 2. Calcular valor da hora
        $hourlyRate = $baseSalary / ($dailyWorkHours * $workingDaysPerMonth);

        // 2.5. Calcular valor da hora extra (×1.5)
        $hourlyRatePremium = $hourlyRate * 1.5;

        // 3. Carregar banco de horas
        $hourBank = HourBank::where('employee_id', $employeeId)
            ->where('month_year', $monthYear)
            ->first();

        $extraHoursMinutes = (int) ($hourBank?->extra_hours_added ?? 0);
        $usedHoursMinutes = (int) ($hourBank?->extra_hours_used ?? 0);

        // 4. Calcular horas extras (em valor monetário)
        $extraHoursAmount = $hourlyRatePremium * ($extraHoursMinutes / 60);

        // 5. Calcular deduções por horas usadas (em valor monetário)
        $deductionsFromHours = $hourlyRate * ($usedHoursMinutes / 60);

        // 6. Converter minutos em horas para exibição
        $extraHoursInHours = $extraHoursMinutes / 60;
        $balanceInHours = ($hourBank?->balance ?? 0) / 60;

        // 7. Atualizar campos (sem number_format para manter como números)
        $set('base_salary', $baseSalary);
        $set('hourly_rate', round($hourlyRate, 2));
        $set('hourly_rate_premium', round($hourlyRatePremium, 2));
        $set('extra_hours_display', round($extraHoursInHours, 2));
        $set('extra_hours_amount', round($extraHoursAmount, 2));
        $set('balance_display', round($balanceInHours, 2));
        $set('deductions', round($deductionsFromHours, 2));

        // 7. Calcular total
        self::updateTotalNet($set, $get);
    }

    private static function updateTotalNet(Set $set, Get $get): void
    {
        $baseSalary = (float) ($get('base_salary') ?? 0);
        $extraHoursAmount = (float) ($get('extra_hours_amount') ?? 0);
        $deductions = (float) ($get('deductions') ?? 0);

        $totalNet = max(0, $baseSalary + $extraHoursAmount - $deductions);

        $set('total_net', round($totalNet, 2));
    }
}
