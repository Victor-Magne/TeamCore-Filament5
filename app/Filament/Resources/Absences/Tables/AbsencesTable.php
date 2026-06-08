<?php

namespace App\Filament\Resources\Absences\Tables;

use App\Models\Absence;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AbsencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('absence_date', 'desc')
            ->striped()
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading('Sem ausências registadas')
            ->emptyStateDescription('Não existem registos de ausências para o período seleccionado.')
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('absence_date')
                    ->label('Data da Ausência')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('deduction_type')
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
                        'other' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('hours_deducted')
                    ->label('Horas Descontadas')
                    ->formatStateUsing(function (?int $state) {
                        if ($state === null || $state === 0) {
                            return '-';
                        }

                        return intdiv($state, 60).'h '.str_pad($state % 60, 2, '0', STR_PAD_LEFT).'m';
                    })
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable()
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('justification_doc')
                    ->label('Justificação')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Absence $record): string => $record->justification_doc ? 'Documento anexado' : 'Sem justificação'),

                TextColumn::make('created_at')
                    ->label('Data de Criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('justify')
                    ->label('Justificar')
                    ->icon('heroicon-o-paper-clip')
                    ->color('warning')
                    ->modalHeading('Justificar Falta')
                    ->modalDescription('Anexe um documento de justificação para esta falta (ex: atestado médico, declaração).')
                    ->schema([
                        FileUpload::make('justification_doc')
                            ->label('Documento de Justificação')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('absences/justifications')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120)
                            ->helperText('Formatos aceites: PDF, JPG, PNG. Tamanho máximo: 5MB.')
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (Absence $record): array => [
                        'justification_doc' => $record->justification_doc,
                    ])
                    ->action(fn (Absence $record, array $data): bool => $record->update([
                        'justification_doc' => $data['justification_doc'],
                    ]))
                    ->successNotificationTitle('Documento de justificação guardado com sucesso'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
