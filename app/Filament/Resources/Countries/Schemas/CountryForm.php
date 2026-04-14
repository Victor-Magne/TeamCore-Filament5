<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome do País')
                ->required()
                ->maxLength(100),

            TextInput::make('code')
                ->label('Código ISO (2 letras)')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(2),

            TextInput::make('phonecode')
                ->label('Indicativo Telefónico')
                ->numeric()
                ->prefix('+')
                ->required(),
        ]);
    }
}
