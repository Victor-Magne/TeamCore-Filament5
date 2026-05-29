<?php

namespace App\Filament\Resources\Payrolls\Schemas;

use App\Models\Contract;
use App\Models\HourBankMovement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

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
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->required()
                            ->searchable()
                            ->live()
                            ->columnSpan(2)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::loadData($set, $get)),

                        TextInput::make('month_year')
                            ->label('Mês de Referência')
                            ->placeholder('2026-04')
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                            ->default(now()->format('Y-m'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::loadData($set, $get)),

                        Select::make('status')
                            ->label('Status do Pagamento')
                            ->options([
                                'pending' => 'Pendente',
                                'paid' => 'Pago',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(4),

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
                            ->label('Saldo de Horas')
                            ->readOnly()
                            ->dehydrated()
                            ->formatStateUsing(function ($state): string {
                                $minutes = (int) $state;
                                if ($minutes === 0) {
                                    return '0h 00m';
                                }
                                $sign = $minutes < 0 ? '-' : '+';
                                $abs = abs($minutes);

                                return $sign.intdiv($abs, 60).'h '.str_pad($abs % 60, 2, '0', STR_PAD_LEFT).'m';
                            })
                            ->hint(fn ($state) => (int) $state >= 0 ? 'Crédito' : 'Débito')
                            ->hintColor(fn ($state) => (int) $state >= 0 ? 'success' : 'danger'),

                        TextInput::make('extra_hours_amount')
                            ->label('Ajuste de Horas')
                            ->numeric()
                            ->prefix('€')
                            ->readOnly()
                            ->dehydrated()
                            ->default(0)
                            ->dehydrateStateUsing(function (Get $get) {
                                $employeeId = $get('employee_id');
                                $monthYear = $get('month_year');

                                if (! $employeeId || ! $monthYear) {
                                    return 0;
                                }

                                $contract = Contract::where('employee_id', $employeeId)
                                    ->where('status', 'active')
                                    ->first();

                                if (! $contract) {
                                    return 0;
                                }

                                $hourlyRate = self::computeHourlyRate($contract);
                                $balance = self::computeHourBankBalance($employeeId, $monthYear);

                                if ($balance > 0) {
                                    $extraHoursAmount = ($hourlyRate * config('hr.extra_hours_multiplier')) * ($balance / 60);
                                } else {
                                    $extraHoursAmount = $hourlyRate * ($balance / 60);
                                }

                                return round($extraHoursAmount, 2);
                            })
                            ->formatStateUsing(function (Get $get) {
                                $value = (float) ($get('extra_hours_amount') ?? 0);

                                return round($value, 2);
                            })
                            ->hint(function (Get $get) {
                                $amount = (float) ($get('extra_hours_amount') ?? 0);
                                if ($amount > 0) {
                                    return 'Acréscimo';
                                }
                                if ($amount < 0) {
                                    return 'Desconto';
                                }

                                return 'Sem movimento';
                            })
                            ->hintColor(function (Get $get) {
                                $amount = (float) ($get('extra_hours_amount') ?? 0);
                                if ($amount > 0) {
                                    return 'success';
                                }
                                if ($amount < 0) {
                                    return 'danger';
                                }

                                return 'gray';
                            }),

                        TextInput::make('deductions')
                            ->label('Outras Deduções')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateTotal($set, $get)),

                        TextInput::make('total_net')
                            ->label('Salário Líquido')
                            ->numeric()
                            ->prefix('€')
                            ->readOnly()
                            ->dehydrated()
                            ->columnSpanFull()
                            ->extraInputAttributes(['class' => 'font-bold text-xl']),
                    ])->columns(2),
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

        $baseSalary = (float) $contract->salary;
        $hourlyRate = self::computeHourlyRate($contract);

        $set('base_salary', round($baseSalary, 2));
        $set('hourly_rate', round($hourlyRate, 2));

        $balance = self::computeHourBankBalance($employeeId, $monthYear);
        $set('extra_hours', $balance);

        if ($balance > 0) {
            $extraHoursAmount = ($hourlyRate * config('hr.extra_hours_multiplier')) * ($balance / 60);
        } else {
            $extraHoursAmount = $hourlyRate * ($balance / 60);
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

    private static function computeHourlyRate(Contract $contract): float
    {
        $baseSalary = (float) $contract->salary;
        $dailyWorkMinutes = (int) ($contract->daily_work_minutes ?? config('hr.default_daily_work_minutes'));

        return $baseSalary / (($dailyWorkMinutes / 60) * config('hr.working_days_per_month'));
    }

    private static function computeHourBankBalance(int $employeeId, string $monthYear): int
    {
        $month = Carbon::createFromFormat('Y-m', $monthYear);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $movements = HourBankMovement::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("SUM(CASE WHEN type = 'addition' THEN amount ELSE 0 END) as extra, SUM(CASE WHEN type = 'deduction' THEN amount ELSE 0 END) as deducted")
            ->first();

        $extra = (int) ($movements->extra ?? 0);
        $deducted = abs((int) ($movements->deducted ?? 0));

        return $extra - $deducted;
    }
}
