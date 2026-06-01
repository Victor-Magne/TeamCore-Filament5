<?php

namespace App\Filament\Resources\Contracts\Tables;

use App\Filament\Actions\ExportContractsPdfBulkAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('Sem contratos')
            ->emptyStateDescription('Crie o primeiro contrato para começar.')
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Funcionário')
                    ->formatStateUsing(fn ($record) => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['employees.first_name', 'employees.last_name'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => 'Efetivo',
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

                TextColumn::make('salary')
                    ->label('Salário')
                    ->money('EUR')
                    ->sortable(),

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
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->placeholder('Indeterminado'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Ativos',
                        'terminated' => 'Terminados',
                        'on_hold' => 'Suspensos',
                    ]),
                SelectFilter::make('type')
                    ->label('Tipo de Contrato')
                    ->options([
                        'permanent' => 'Efetivo',
                        'fixed_term' => 'Prazo Certo',
                        'unfixed_term' => 'Prazo Incerto',
                        'internship' => 'Estágio',
                        'service_provision' => 'Prestação de Serviços',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('export_pdf')
                    ->label('Exportar Relatório PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(route('contracts.pdf.all'))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportContractsPdfBulkAction::make(),
                ]),
            ]);
    }
}
