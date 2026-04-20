<?php

namespace App\Filament\Resources\Payrolls;

use App\Filament\Resources\Payrolls\Pages\CreatePayroll;
use App\Filament\Resources\Payrolls\Pages\EditPayroll;
use App\Filament\Resources\Payrolls\Pages\ListPayrolls;
use App\Filament\Resources\Payrolls\Schemas\PayrollForm;
use App\Filament\Resources\Payrolls\Tables\PayrollsTable;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\Payroll\GeneratePayrollService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput as FormTextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Recursos Humanos';

    public static function getNavigationLabel(): string
    {
        return __('Processamento Salarial');
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public static function form(Schema $schema): Schema
    {
        return PayrollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollsTable::configure($table)
            ->headerActions([
                Action::make('process_payroll')
                    ->label('Processar Salários')
                    ->icon('heroicon-o-cpu-chip')
                    ->form([
                        FormTextInput::make('month_year')
                            ->label('Mês de Referência')
                            ->placeholder('YYYY-MM')
                            ->regex('/^\d{4}-(0[1-9]|1[0-2])$/')
                            ->default(now()->format('Y-m'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $monthYear = $data['month_year'];
                        $employees = Employee::whereNull('date_dismissed')->get();
                        $service = new GeneratePayrollService;

                        foreach ($employees as $employee) {
                            $service->handle($employee, $monthYear);
                        }

                        Notification::make()
                            ->title('Processamento concluído')
                            ->body("Salários processados para {$monthYear}")
                            ->success()
                            ->send()
                            ->sendToDatabase(auth()->user());
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrolls::route('/'),
            'create' => CreatePayroll::route('/create'),
            'edit' => EditPayroll::route('/{record}/edit'),
        ];
    }
}
