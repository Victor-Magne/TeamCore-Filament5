<?php

namespace App\Filament\Widgets;

use App\Models\Vacation;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class EmployeeVacationWidget extends BaseWidget
{
    protected static ?string $heading = 'Minhas Férias';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = Auth::user()->employee;

        return $table
            ->query(
                Vacation::where('employee_id', $employee?->id)
                    ->latest()
                    ->limit(5)
            ) 
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray'),
                    
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray'),
                    
                Tables\Columns\TextColumn::make('days_taken')
                    ->label('Dias')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
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
            ->headerActions([
                Action::make('balance')
                    ->label(fn () => 'Saldo: '.($employee?->vacation_balance ?? 0).' dias')
                    ->button()
                    ->disabled()
                    ->color('info')
                    ->icon('heroicon-m-information-circle'),
            ])
            ->actions([
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->hidden(fn (Vacation $record): bool => $record->status !== 'pending')
                    ->action(function (Vacation $record) {
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
                                \Filament\Forms\Components\DatePicker::make('start_date')
                                    ->label('Início'),
                                \Filament\Forms\Components\DatePicker::make('end_date')
                                    ->label('Fim'),
                                \Filament\Forms\Components\TextInput::make('days_taken')
                                    ->label('Dias'),
                                \Filament\Forms\Components\TextInput::make('status')
                                    ->label('Estado')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'Pendente',
                                        'approved' => 'Aprovado',
                                        'rejected' => 'Rejeitado',
                                        default => $state,
                                    }),
                            ])
                    ])
                    ->modalHeading('Detalhes das Férias')
                    ->icon('heroicon-m-eye')
                    ->iconButton(),
            ])
            ->emptyStateHeading('Sem registos de férias')
            ->emptyStateDescription('Ainda não efetuou nenhum pedido de férias.')
            ->emptyStateIcon('heroicon-o-sun');
    }
}
