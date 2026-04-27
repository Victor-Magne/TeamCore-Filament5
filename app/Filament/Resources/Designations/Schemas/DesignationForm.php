<?php

namespace App\Filament\Resources\Designations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DesignationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes do Cargo')
                    ->description('Defina o título, nível hierárquico e remuneração base.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome do Cargo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Ex: Engenheiro de Software'),

                        Select::make('level')
                            ->label('Nível / Grau')
                            ->options([
                                'junior' => 'Júnior',
                                'pleno' => 'Pleno',
                                'senior' => 'Sénior',
                                'specialist' => 'Especialista',
                                'lead' => 'Líder',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('role_name')
                            ->label('Role Associada')
                            ->helperText('A role que será atribuída automaticamente aos funcionários com este cargo.')
                            ->relationship('role', 'name')
                            ->searchable()
                            ->native(false),

                        TextInput::make('base_salary')
                            ->label('Salário Base')
                            ->numeric()
                            ->prefix('€') // Mude para a sua moeda local
                            ->placeholder('0.00'),
                    ])->columns(2),
            ]);
    }
}
