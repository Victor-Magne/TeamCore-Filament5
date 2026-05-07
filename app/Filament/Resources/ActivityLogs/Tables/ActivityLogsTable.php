<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Action')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('event')
                    ->label('Event Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('causer_type')
                    ->label('Actor')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('log_name')
                    ->label('Log')
                    ->badge()
                    ->hidden(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Tipo de Evento')
                    ->options([
                        'created' => 'Criado',
                        'updated' => 'Atualizado',
                        'deleted' => 'Eliminado',
                    ])
                    ->multiple(),
                
                SelectFilter::make('subject_type')
                    ->label('Tipo de Assunto')
                    ->multiple()
                    ->options(function () {
                        return \App\Models\ActivityLog::distinct()
                            ->pluck('subject_type', 'subject_type')
                            ->mapWithKeys(fn ($item) => [$item => class_basename($item)])
                            ->toArray();
                    }),
                
                SelectFilter::make('causer_type')
                    ->label('Tipo de Actor')
                    ->multiple()
                    ->options(function () {
                        return \App\Models\ActivityLog::whereNotNull('causer_type')
                            ->distinct()
                            ->pluck('causer_type', 'causer_type')
                            ->mapWithKeys(fn ($item) => [$item => class_basename($item)])
                            ->toArray();
                    }),
                
                SelectFilter::make('log_name')
                    ->label('Nome do Log')
                    ->multiple()
                    ->options(function () {
                        return \App\Models\ActivityLog::distinct()
                            ->pluck('log_name', 'log_name')
                            ->toArray();
                    }),
                
                Filter::make('created_at')
                    ->label('Período')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('De'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->columns(2),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('View'),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
