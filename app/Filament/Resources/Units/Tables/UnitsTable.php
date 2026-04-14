<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Unidade')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn($record) => $record->type ? ucfirst($record->type) : null),

            TextColumn::make('managers.first_name')
                ->label('Gestores')
                ->badge() // Mostra cada gestor numa etiqueta
                ->color('gray')
                ->searchable()
                ->wrap() // Se forem muitos, quebra a linha para não esticar a tabela
                ->placeholder('Sem gestores atribuídos'),

                // Número de funcionários na unidade
                TextColumn::make('employees_count')
                    ->label('Staff')
                    ->counts('employees')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_main_direction')
                    ->label('Principal')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // LÓGICA DE LISTA RECOLHÍVEL (HAMBÚRGUER)
            ->groups([
                Group::make('parent.name')
                    ->label('Agrupado por Unidade Superior')
                    ->collapsible(), // Permite expandir/recolher
            ])
            ->defaultGroup('parent.name')

            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
