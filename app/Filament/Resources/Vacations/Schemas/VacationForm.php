<?php

namespace App\Filament\Resources\Vacations\Schemas;

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
                        ->columnSpanFull(),
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
                        ->live(),

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
                        ->native(false),

                    Textarea::make('rejection_reason')
                        ->label('Razão da Rejeição')
                        ->visible(fn (callable $get) => $get('status') === 'rejected')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
