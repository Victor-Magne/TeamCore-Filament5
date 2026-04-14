<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Validation\Rules\Unique;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome da Unidade')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'direction' => 'Direção',
                        'management' => 'Gestão',
                        'department' => 'Departamento',
                        'section' => 'Secção',
                    ])
                    ->required()
                    ->native(false),

                Textarea::make('description')
                    ->label('Descrição')
                    ->default(null)
                    ->columnSpanFull(),

                Toggle::make('is_main_direction')
                    ->label('É a Direção Principal?')
                    ->default(false)
                    // LÓGICA SOLICITADA:
                    // Se já existir uma Main Direction, esconde o Toggle.
                    // Mas permite que ele apareça se estivermos a editar a própria Main Direction.
                    ->hidden(function (?Unit $record) {
                        $exists = Unit::where('is_main_direction', true)->exists();
                        if (!$record) return $exists; // Criando novo: esconde se já existe
                        return $exists && !$record->is_main_direction; // Editando: esconde se não for a atual
                    })
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $set('parent_id', null);
                        }
                    })
                    ->unique(
                        table: 'organizational_units',
                        column: 'is_main_direction',
                        ignorable: fn($record) => $record,
                        modifyRuleUsing: fn(Unique $rule) => $rule->where('is_main_direction', 1)
                    ),

                Select::make('parent_id')
                    ->label('Unidade Superior (Pai)')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null)
                    // Esconde o campo pai se for a Main Direction
                    ->hidden(fn(Get $get): bool => (bool) $get('is_main_direction'))
                    ->required(fn(Get $get): bool => !$get('is_main_direction')),

            Select::make('managers') // Nome da relação no plural
                ->label('Gestores Responsáveis')
                ->multiple() // PERMITE SELECIONAR VÁRIOS
                ->relationship('managers', 'first_name') // Liga à relação Many-to-Many
                ->searchable()
                ->preload()
                ->helperText('Pode selecionar um ou mais gestores para esta unidade.')
            ]);
    }
}
