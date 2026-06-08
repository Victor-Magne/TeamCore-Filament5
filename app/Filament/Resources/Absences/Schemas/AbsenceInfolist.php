<?php

namespace App\Filament\Resources\Absences\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AbsenceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Funcionário')
                ->schema([
                    TextEntry::make('employee.first_name')
                        ->label('Funcionário')
                        ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}"),
                ]),

            Section::make('Ausência')
                ->columns(2)
                ->schema([
                    TextEntry::make('absence_date')
                        ->label('Data da Ausência')
                        ->date('d/m/Y'),

                    TextEntry::make('deduction_type')
                        ->label('Tipo de Dedução')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'unjustified_absence' => 'Falta Injustificada',
                            'partial_absence' => 'Falta Parcial',
                            'other' => 'Outra',
                            default => $state,
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'unjustified_absence' => 'danger',
                            'partial_absence' => 'warning',
                            default => 'gray',
                        }),

                    TextEntry::make('hours_deducted')
                        ->label('Horas Descontadas')
                        ->formatStateUsing(function (?int $state): string {
                            if ($state === null || $state === 0) {
                                return '-';
                            }

                            return intdiv($state, 60).'h '.str_pad($state % 60, 2, '0', STR_PAD_LEFT).'m';
                        }),

                    TextEntry::make('reason')
                        ->label('Motivo')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make('Justificação')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    TextEntry::make('justification_doc')
                        ->label('Documento Anexado')
                        ->placeholder('Sem documento de justificação')
                        ->formatStateUsing(fn (?string $state): string => $state
                            ? basename($state)
                            : 'Sem documento'
                        )
                        ->url(fn (?string $state): ?string => $state
                            ? asset('storage/'.$state)
                            : null
                        )
                        ->openUrlInNewTab()
                        ->columnSpanFull(),
                ]),

            Section::make('Licença Relacionada')
                ->collapsed()
                ->visible(fn ($record) => $record->leaveAndAbsence !== null)
                ->schema([
                    TextEntry::make('leaveAndAbsence.type')
                        ->label('Tipo de Licença')
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'sick_leave' => 'Baixa Médica (SNS)',
                            'parental' => 'Licença Parental',
                            'marriage' => 'Licença de Casamento',
                            'bereavement' => 'Nojo (Falecimento)',
                            'justified_absence' => 'Falta Justificada',
                            'unjustified' => 'Falta Injustificada',
                            default => $state ?? '-',
                        }),
                    TextEntry::make('leaveAndAbsence.start_date')
                        ->label('Início da Licença')
                        ->date('d/m/Y'),
                    TextEntry::make('leaveAndAbsence.end_date')
                        ->label('Fim da Licença')
                        ->date('d/m/Y'),
                ]),

            Section::make('Metadados')
                ->columns(2)
                ->collapsed()
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Criado em')
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('updated_at')
                        ->label('Actualizado em')
                        ->dateTime('d/m/Y H:i'),
                ]),
        ]);
    }
}
