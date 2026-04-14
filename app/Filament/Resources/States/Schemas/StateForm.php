<?php

namespace App\Filament\Resources\States\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('country_id')
                ->label('País')
                ->relationship('country', 'name')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('name')
                ->label('Nome do Estado / Província')
                ->required()
                ->maxLength(100),
        ]);
    }
}
