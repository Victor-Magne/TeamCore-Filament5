<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $title = 'Contratos';

    protected static ?string $modelLabel = 'Contrato';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => 'Efetivo',
                        'fixed_term' => 'Prazo Certo',
                        'internship' => 'Estágio',
                        'service_provision' => 'Prestação de Serviços',
                        default => $state,
                    })
                    ->color('info'),

                TextColumn::make('designation.name')
                    ->label('Designação'),

                TextColumn::make('salary')
                    ->label('Salário')
                    ->money('EUR'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'terminated' => 'Terminado',
                        'on_hold' => 'Suspenso',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'terminated' => 'danger',
                        'on_hold' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y'),

                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->placeholder('Indeterminado'),
            ])
            ->headerActions([])
            ->actions([
                EditAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.contracts.edit', $record)),
            ])
            ->bulkActions([]);
    }
}
