<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes da Atividade')
                    ->schema([
                        TextInput::make('created_at')->label('Data/Hora')->disabled(),
                        TextInput::make('user.name')->label('Utilizador')->disabled(),
                        TextInput::make('action')->label('Ação')->disabled(),
                        TextInput::make('model_type')->label('Modelo')->disabled(),
                        TextInput::make('model_id')->label('ID')->disabled(),
                        TextInput::make('payload')
                            ->label('Dados')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
