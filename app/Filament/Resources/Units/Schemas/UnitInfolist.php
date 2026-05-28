<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Models\Unit;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Gerais')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nome'),
                        TextEntry::make('type')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'direction' => 'Direção',
                                'management' => 'Gestão',
                                'department' => 'Departamento',
                                'section' => 'Secção',
                                default => $state,
                            }),
                        TextEntry::make('description')
                            ->label('Descrição')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Hierarquia e Gestão')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('parent.name')
                            ->label('Unidade Superior')
                            ->placeholder('(Nível de topo)'),
                        IconEntry::make('is_main_direction')
                            ->label('Direção Principal')
                            ->boolean(),
                        TextEntry::make('managers.first_name')
                            ->label('Equipa de Gestão')
                            ->badge()
                            ->listWithLineBreaks()
                            ->placeholder('Sem gestores atribuídos')
                            ->columnSpanFull(),
                    ]),

                Section::make('Metadados')
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
                            ->visible(fn (Unit $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
