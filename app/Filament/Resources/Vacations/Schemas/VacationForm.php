<?php

namespace App\Filament\Resources\Vacations\Schemas;

use App\Models\Vacation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                        ->default(Carbon::now()->year),

                    DatePicker::make('start_date')
                        ->label('Data de Início')
                        ->required()
                        ->native(false)
                        ->live(),

                    DatePicker::make('end_date')
                        ->label('Data de Fim')
                        ->required()
                        ->native(false)
                        ->live()
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
                        ->afterStateHydrated(function (TextInput $component, ?string $state): void {
                            $state ??= '0';
                            $component->state($state);
                        })
                        ->live(debounce: 500)
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
                        ->disabled(fn (?Vacation $record, callable $get): bool =>
                            (($record && $record->employee_id === auth()->user()?->employee_id) ||
                             ((int) $get('employee_id') === auth()->user()?->employee_id)) &&
                            ! auth()->user()?->can('Approve:OwnVacation')
                        )
                        ->dehydrated(),

                    Textarea::make('rejection_reason')
                        ->label('Razão da Rejeição')
                        ->visible(fn (callable $get) => $get('status') === 'rejected')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
