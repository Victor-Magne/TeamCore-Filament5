<?php

namespace App\Filament\Resources\Units\Tables;

use App\Models\Unit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

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
                    ->description(fn (Unit $record): ?string => match ($record->type) {
                        'direction' => 'Direção',
                        'department' => 'Departamento',
                        'section' => 'Secção',
                        default => ucfirst($record->type ?? ''),
                    }),

                TextColumn::make('managers.first_name')
                    ->label('Gestores')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->wrap()
                    ->placeholder('Sem gestores'),

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
            ->groups([
                Group::make('parent.name')
                    ->label('Unidade Superior')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (Unit $record): string => $record->parent?->name ?? 'Sem unidade superior'),
            ])
            ->defaultGroup('parent.name')
            ->groupingSettingsHidden()
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
