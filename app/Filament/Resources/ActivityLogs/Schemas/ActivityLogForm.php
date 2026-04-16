<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity Details')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Action'),
                        TextEntry::make('event')
                            ->badge(),
                        TextEntry::make('log_name')
                            ->label('Log Name'),
                        TextEntry::make('subject_type')
                            ->label('Subject Type'),
                        TextEntry::make('causer_type')
                            ->label('Actor Type'),
                        TextEntry::make('created_at')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(2),
                Section::make('Changes')
                    ->schema([
                        TextEntry::make('attribute_changes')
                            ->label('Attribute Changes')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                            ->copyable(),
                    ]),
                Section::make('Additional Info')
                    ->schema([
                        TextEntry::make('properties')
                            ->label('Properties')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                            ->copyable(),
                    ]),
            ]);
    }
}
