<?php

namespace App\Filament\Resources\Absences\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informações da Ausência')
                ->schema([
                    Select::make('employee_id')
                        ->label('Funcionário')
                        ->relationship('employee', 'first_name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled()
                        ->columnSpanFull(),

                    TextInput::make('absence_date')
                        ->label('Data da Ausência')
                        ->type('date')
                        ->required()
                        ->disabled()
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Dedução de Horas')
                ->schema([
                    Select::make('deduction_type')
                        ->label('Tipo de Dedução')
                        ->options([
                            'unjustified_absence' => 'Falta Injustificada',
                            'partial_absence' => 'Falta Parcial',
                            'other' => 'Outra',
                        ])
                        ->required()
                        ->disabled(),

                    TextInput::make('hours_deducted')
                        ->label('Horas Descontadas (minutos)')
                        ->numeric()
                        ->disabled(),

                    Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'atraso' => 'Atraso',
                            'falta' => 'Falta',
                        ])
                        ->disabled(),

                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pendente' => 'Pendente',
                            'justificado' => 'Justificado',
                            'rejeitado' => 'Rejeitado',
                        ])
                        ->required(),
                ])->columns(2),

            Section::make('Justificação e Observações')
                ->schema([
                    Textarea::make('justification')
                        ->label('Justificação do Funcionário')
                        ->placeholder('Escreva aqui a justificação...'),

                    Textarea::make('reason')
                        ->label('Motivo/Notas Internas')
                        ->disabled()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
