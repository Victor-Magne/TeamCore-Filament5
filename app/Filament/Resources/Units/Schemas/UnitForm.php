<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome da Unidade')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'direction' => 'Direção',
                                'management' => 'Gestão',
                                'department' => 'Departamento',
                                'section' => 'Secção',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),

                        Toggle::make('is_main_direction')
                            ->label('É a Direção Principal?')
                            ->default(false)
                            ->hidden(function (?Unit $record) {
                                $exists = Unit::where('is_main_direction', true)->exists();
                                if (! $record) {
                                    return $exists;
                                }
                                return $exists && ! $record->is_main_direction;
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
                                ignorable: fn ($record) => $record,
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('is_main_direction', 1)
                            ),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),

                Section::make('Hierarquia')
                    ->columns(1)
                    ->schema([
                        Select::make('parent_id')
                            ->label('Unidade Superior (Pai)')
                            ->relationship(
                                'parent',
                                'name',
                                fn (Builder $query, Get $get) => match ($get('type')) {
                                    'section' => $query->where('type', 'department'),
                                    'department' => $query->where('type', 'direction'),
                                    'management' => $query->where('type', 'direction'),
                                    default => $query,
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->hidden(fn (Get $get): bool => (bool) $get('is_main_direction'))
                            ->required(fn (Get $get): bool => ! $get('is_main_direction')),
                    ]),

                Section::make('Gestão')
                    ->columns(1)
                    ->schema([
                        Select::make('managers')
                            ->label('Gestores Responsáveis')
                            ->multiple()
                            ->relationship('managers', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->helperText('Pode selecionar um ou mais gestores para esta unidade.'),
                    ]),
            ]);
    }
}
