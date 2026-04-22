<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Widgets\Widget;

class EmployeeInfoWidget extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected string $view = 'filament.widgets.employee-info-widget';

    private function getEmployee(): ?Employee
    {
        return auth()->user()?->employee;
    }

    public function employeeInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getEmployee())
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label('Nome')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->columnSpan(2),

                                TextEntry::make('designation.name')
                                    ->label('Cargo')
                                    ->default('Sem Cargo Definido')
                                    ->icon('heroicon-m-briefcase')
                                    ->columnSpan(2),

                                TextEntry::make('email')
                                    ->label('Email Corporativo')
                                    ->icon('heroicon-m-envelope')
                                    ->default('N/A'),

                                TextEntry::make('unit.name')
                                    ->label('Departamento')
                                    ->icon('heroicon-m-building-office')
                                    ->default('N/A'),

                                TextEntry::make('phone_number')
                                    ->label('Contacto')
                                    ->icon('heroicon-m-phone')
                                    ->default('Não registado'),

                                TextEntry::make('date_hired')
                                    ->label('Data de Admissão')
                                    ->icon('heroicon-m-calendar')
                                    ->date('d/m/Y')
                                    ->default('N/A'),

                                TextEntry::make('address')
                                    ->label('Morada')
                                    ->icon('heroicon-m-map-pin')
                                    ->default('N/A')
                                    ->formatStateUsing(function ($state, $record) {
                                        return collect([
                                            $state,
                                            $record->zip_code,
                                            $record->city?->name,
                                        ])->filter()->join(', ') ?: 'N/A';
                                    })
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }
}
