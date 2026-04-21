<?php

namespace App\Filament\Widgets;

use App\Models\LeaveAndAbsence;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class EmployeeLeaveWidget extends BaseWidget
{
    protected static ?string $heading = 'Minhas Licenças e Ausências';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = Auth::user()->employee;

        return $table
            ->query(
                LeaveAndAbsence::where('employee_id', $employee?->id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sick_leave' => 'Baixa Médica',
                        'parental' => 'Licença Parental',
                        'marriage' => 'Licença Casamento',
                        'bereavement' => 'Nojo (Falecimento)',
                        'justified_absence' => 'Falta Justificada',
                        'unjustified' => 'Falta Injustificada',
                        default => $state,
                    })
                    ->description(fn (LeaveAndAbsence $record): ?string => $record->reason)
                    ->icon('heroicon-m-tag')
                    ->iconColor('primary'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Período')
                    ->formatStateUsing(fn (LeaveAndAbsence $record): string =>
                        $record->start_date->format('d/m/Y') . ' - ' . $record->end_date->format('d/m/Y')
                    )
                    ->icon('heroicon-m-calendar')
                    ->iconColor('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-m-clock',
                        'approved' => 'heroicon-m-check-circle',
                        'rejected' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-question-mark-circle',
                    }),
            ])
            ->actions([
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->hidden(fn (LeaveAndAbsence $record): bool => $record->status !== 'pending')
                    ->action(function (LeaveAndAbsence $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Pedido cancelado')
                            ->success()
                            ->send();
                    }),
                ViewAction::make()
                    ->form([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('type')
                                    ->label('Tipo')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'sick_leave' => 'Baixa Médica',
                                        'parental' => 'Licença Parental',
                                        'marriage' => 'Licença Casamento',
                                        'bereavement' => 'Nojo (Falecimento)',
                                        'justified_absence' => 'Falta Justificada',
                                        'unjustified' => 'Falta Injustificada',
                                        default => $state,
                                    }),
                                \Filament\Forms\Components\TextInput::make('status')
                                    ->label('Estado')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'Pendente',
                                        'approved' => 'Aprovado',
                                        'rejected' => 'Rejeitado',
                                        default => $state,
                                    }),
                                \Filament\Forms\Components\DatePicker::make('start_date')
                                    ->label('Início'),
                                \Filament\Forms\Components\DatePicker::make('end_date')
                                    ->label('Fim'),
                                \Filament\Forms\Components\Textarea::make('reason')
                                    ->label('Motivo')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->modalHeading('Detalhes da Ausência')
                    ->icon('heroicon-m-eye')
                    ->iconButton(),
            ])
            ->emptyStateHeading('Sem registos de ausências')
            ->emptyStateDescription('Ainda não efetuou nenhum pedido de licença ou ausência.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
