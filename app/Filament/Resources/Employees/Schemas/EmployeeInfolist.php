<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identificação')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Flex::make([
                            ImageEntry::make('photo')
                                ->label('')
                                ->disk('public')
                                ->circular()
                                ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->first_name.' '.$record->last_name).'&color=7F9CF5&background=EBF4FF')
                                ->size(80)
                                ->grow(false),

                            Grid::make(3)
                                ->schema([
                                    TextEntry::make('first_name')
                                        ->label('Nome'),
                                    TextEntry::make('last_name')
                                        ->label('Apelido'),
                                    TextEntry::make('gender')
                                        ->label('Género')
                                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                                            'male' => 'Masculino',
                                            'female' => 'Feminino',
                                            'other' => 'Outro',
                                            default => '—',
                                        }),
                                    TextEntry::make('email')
                                        ->label('Email Profissional')
                                        ->columnSpan(2),
                                    TextEntry::make('date_of_birth')
                                        ->label('Data de Nascimento')
                                        ->date('d/m/Y'),
                                ]),
                        ])->from('sm'),
                    ]),

                Section::make('Documentação e Contacto')
                    ->icon('heroicon-o-identification')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('phone_number')
                            ->label('Telemóvel')
                            ->placeholder('—'),
                        TextEntry::make('nif')
                            ->label('NIF')
                            ->placeholder('—'),
                        TextEntry::make('nss')
                            ->label('Nº Seg. Social')
                            ->placeholder('—'),
                        TextEntry::make('address')
                            ->label('Morada')
                            ->placeholder('—')
                            ->columnSpan(2),
                        TextEntry::make('zip_code')
                            ->label('Código Postal')
                            ->placeholder('—'),
                        TextEntry::make('city.name')
                            ->label('Cidade')
                            ->placeholder('—'),
                    ]),

                Section::make('Vínculo Laboral')
                    ->icon('heroicon-o-briefcase')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('unit.name')
                            ->label('Unidade/Departamento')
                            ->placeholder('—'),
                        TextEntry::make('designation.name')
                            ->label('Cargo')
                            ->badge()
                            ->color('info')
                            ->placeholder('—'),
                        TextEntry::make('is_active')
                            ->label('Estado')
                            ->badge()
                            ->state(fn ($record): string => $record->date_dismissed ? 'Inativo' : 'Ativo')
                            ->color(fn ($record): string => $record->date_dismissed ? 'danger' : 'success'),
                        TextEntry::make('date_hired')
                            ->label('Data de Admissão')
                            ->date('d/m/Y'),
                        TextEntry::make('date_dismissed')
                            ->label('Data de Demissão')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        TextEntry::make('vacation_balance')
                            ->label('Saldo de Férias')
                            ->suffix(' dias'),
                    ]),
            ]);
    }
}
