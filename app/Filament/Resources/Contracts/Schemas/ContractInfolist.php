<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Models\Contract;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('employee.first_name')
                            ->label('Funcionário')
                            ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}"),
                        TextEntry::make('designation.name')
                            ->label('Designação')
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'permanent' => 'Efetivo / Tempo Indeterminado',
                                'fixed_term' => 'Prazo Certo',
                                'unfixed_term' => 'Prazo Incerto',
                                'internship' => 'Estágio',
                                'service_provision' => 'Prestação de Serviços',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'permanent' => 'success',
                                'fixed_term' => 'info',
                                'unfixed_term' => 'warning',
                                'internship' => 'primary',
                                'service_provision' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('status')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'terminated' => 'danger',
                                'on_hold' => 'warning',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'Ativo',
                                'terminated' => 'Terminado',
                                'on_hold' => 'Suspenso',
                                default => $state,
                            }),
                    ]),

                Section::make('Vigência')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('start_date')
                            ->label('Data de Início')
                            ->date('d/m/Y'),
                        TextEntry::make('end_date')
                            ->label('Data de Fim')
                            ->date('d/m/Y')
                            ->placeholder('Indeterminado'),
                    ]),

                Section::make('Remuneração e Jornada')
                    ->icon('heroicon-o-banknotes')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('salary')
                            ->label('Salário Bruto')
                            ->money('EUR'),
                        TextEntry::make('daily_work_minutes')
                            ->label('Jornada Diária')
                            ->formatStateUsing(fn (?int $state): string => $state
                                ? intdiv($state, 60).'h '.($state % 60).'m'
                                : '-'),
                        TextEntry::make('expected_start_time')
                            ->label('Hora de Entrada')
                            ->placeholder('-'),
                        TextEntry::make('lunch_duration_minutes')
                            ->label('Duração do Almoço')
                            ->formatStateUsing(fn (?int $state): string => $state !== null
                                ? intdiv($state, 60).'h '.($state % 60).'m'
                                : '-'),
                    ]),

                Section::make('Metadados')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Actualizado em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('Eliminado em')
                            ->dateTime('d/m/Y H:i')
                            ->visible(fn (Contract $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
