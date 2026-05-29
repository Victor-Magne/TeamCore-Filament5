<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data e Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'Criado',
                        'updated' => 'Atualizado',
                        'deleted' => 'Eliminado',
                        default => $state ?? 'Sistema',
                    }),
                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label('Entidade')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        return ActivityLog::distinct()
                            ->pluck('subject_type', 'subject_type')
                            ->mapWithKeys(fn ($item) => [$item => class_basename($item)])
                            ->toArray();
                    }),

                SelectFilter::make('causer_type')
                    ->label('Tipo de Actor')
                    ->multiple()
                    ->options(function () {
                        return ActivityLog::whereNotNull('causer_type')
                            ->distinct()
                            ->pluck('causer_type', 'causer_type')
                            ->mapWithKeys(fn ($item) => [$item => class_basename($item)])
                            ->toArray();
                    }),

                SelectFilter::make('log_name')
                    ->label('Nome do Log')
                    ->multiple()
                    ->options(function () {
                        return ActivityLog::distinct()
                            ->pluck('log_name', 'log_name')
                            ->toArray();
                    }),

                Filter::make('created_at')
                    ->label('Período')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('De'),
                        DatePicker::make('created_until')
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
                ViewAction::make(),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
