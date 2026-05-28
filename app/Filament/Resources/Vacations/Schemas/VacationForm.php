<?php

namespace App\Filament\Resources\Vacations\Schemas;

use App\Models\Employee;
use App\Models\Vacation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class VacationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Funcionário')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull()
                        ->live(),
                ]),

            Section::make('Período de Férias')
                ->description('Defina as datas. O ano e dias gozados serão preenchidos automaticamente.')
                ->schema([
                    Hidden::make('year_reference')
                        ->default(Carbon::now()->year)
                        ->dehydrated(),

                    DatePicker::make('start_date')
                        ->label('Data de Início')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state, callable $get) {
                            if ($state) {
                                $set('year_reference', Carbon::parse($state)->year);
                            }
                            self::recalculateDays($set, $get);
                        }),

                    DatePicker::make('end_date')
                        ->label('Data de Fim')
                        ->required()
                        ->native(false)
                        ->afterOrEqual('start_date')
                        ->live()
                        ->afterStateUpdated(fn (Set $set, callable $get) => self::recalculateDays($set, $get))
                        ->rules([
                            fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                $startDate = $get('start_date');
                                $employeeId = $get('employee_id');
                                $recordId = $get('id');

                                if (! $startDate || ! $employeeId) {
                                    return;
                                }

                                $overlap = Vacation::where('employee_id', $employeeId)
                                    ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                    ->where(function ($query) use ($startDate, $value) {
                                        $query->whereBetween('start_date', [$startDate, $value])
                                            ->orWhereBetween('end_date', [$startDate, $value])
                                            ->orWhere(function ($q) use ($startDate, $value) {
                                                $q->where('start_date', '<=', $startDate)
                                                    ->where('end_date', '>=', $value);
                                            });
                                    })
                                    ->exists();

                                if ($overlap) {
                                    $fail('O funcionário já possui férias marcadas para este período.');
                                }
                            },
                        ]),

                    TextInput::make('days_taken')
                        ->label('Dias Gozados')
                        ->numeric()
                        ->readonly()
                        ->dehydrated(),
                ])->columns(2),

            Section::make('Aprovação')
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pending' => 'Pendente',
                            'approved' => 'Aprovado',
                            'rejected' => 'Rejeitado',
                        ])
                        ->default('pending')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (callable $set, ?string $state) {
                            if (in_array($state, ['approved', 'rejected'])) {
                                $set('approved_by', auth()->id());
                            } else {
                                $set('approved_by', null);
                            }
                        })
                        ->rules([
                            fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                if ($value !== 'approved') {
                                    return;
                                }

                                $employeeId = $get('employee_id');
                                $recordId = $get('id');

                                if (! $employeeId) {
                                    return;
                                }

                                $employee = Employee::find($employeeId);
                                if (! $employee) {
                                    return;
                                }

                                $startDate = $get('start_date');
                                $endDate = $get('end_date');
                                $daysTaken = ($startDate && $endDate)
                                    ? max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1)
                                    : (int) $get('days_taken');

                                // Só ignora re-validação se já estava aprovada E os dias não aumentaram
                                if ($recordId) {
                                    $existing = Vacation::find($recordId);
                                    if ($existing && $existing->status === 'approved' && $daysTaken <= $existing->days_taken) {
                                        return;
                                    }
                                }

                                if ($employee->vacation_balance < $daysTaken) {
                                    $fail("Saldo insuficiente. Disponível: {$employee->vacation_balance} dia(s), necessário: {$daysTaken}.");
                                }
                            },
                        ])
                        ->disabled(fn (?Vacation $record, callable $get): bool =>
                            (($record && $record->employee_id === auth()->user()?->employee_id) ||
                             ((int) $get('employee_id') === auth()->user()?->employee_id)) &&
                            ! auth()->user()?->can('Approve:OwnVacation')
                        )
                        ->dehydrated(),

                    \Filament\Forms\Components\Hidden::make('approved_by')
                        ->dehydrated(),

                    Textarea::make('rejection_reason')
                        ->label('Razão da Rejeição')
                        ->visible(fn (callable $get) => $get('status') === 'rejected')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    private static function recalculateDays(Set $set, callable $get): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        if ($start && $end) {
            $days = max(1, Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1);
            $set('days_taken', $days);
        } else {
            $set('days_taken', 0);
        }
    }
}
