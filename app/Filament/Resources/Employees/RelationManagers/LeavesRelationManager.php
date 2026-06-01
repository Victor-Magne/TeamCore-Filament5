<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeavesRelationManager extends RelationManager
{
    protected static string $relationship = 'leaves';

    protected static ?string $title = 'Licenças e Afastamentos';

    protected static ?string $modelLabel = 'Licença';

    public function form(Schema $schema): Schema
    {
        return $schema;
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
                        'sick_leave' => 'Baixa Médica',
                        'parental' => 'Lic. Parental',
                        'marriage' => 'Casamento',
                        'bereavement' => 'Nojo',
                        'justified_absence' => 'Falta Justif.',
                        'unjustified' => 'Falta Injustif.',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'sick_leave' => 'danger',
                        'parental' => 'info',
                        'marriage' => 'primary',
                        'bereavement' => 'warning',
                        'justified_absence' => 'success',
                        'unjustified' => 'secondary',
                        default => 'gray',
                    }),

                TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y'),

                IconColumn::make('is_paid')
                    ->label('Remunerada')
                    ->boolean(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('approver.name')
                    ->label('Aprovado Por')
                    ->placeholder('-'),

                TextColumn::make('rejection_reason')
                    ->label('Motivo de Rejeição')
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                    ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
