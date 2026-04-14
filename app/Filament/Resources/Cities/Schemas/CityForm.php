<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Models\Country;
use App\Models\State;
use App\Models\City; // Certifique-se de importar o model City
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Campo de país para filtrar os estados
            Select::make('country_id')
                ->label('País')
                ->options(Country::pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn(Set $set) => $set('state_id', null))
                ->dehydrated(false) // Não guarda na DB
                // ESTA É A CORREÇÃO: Preenche o campo ao carregar para edição
                ->afterStateHydrated(function (Set $set, ?Model $record) {
                    if ($record && $record->state_id) {
                        // Busca o país através da relação com o estado
                        $countryId = State::where('id', $record->state_id)->value('country_id');
                        $set('country_id', $countryId);
                    }
                }),

            Select::make('state_id')
                ->label('Estado / Província')
                ->options(function (Get $get) {
                    $countryId = $get('country_id');

                    if (! $countryId) {
                        return [];
                    }

                    return State::where('country_id', $countryId)->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->required()
                // Habilita se houver país selecionado OU se estivermos editando
                ->disabled(fn(Get $get) => ! $get('country_id')),

            TextInput::make('name')
                ->label('Nome da Cidade')
                ->required()
                ->maxLength(100),
        ]);
    }
}
