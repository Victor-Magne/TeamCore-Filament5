<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('description')
                            ->label('Action'),
                        TextEntry::make('event')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('log_name')
                            ->label('Log Name'),
                        TextEntry::make('subject_type')
                            ->label('Subject Type'),
                        TextEntry::make('subject_id')
                            ->label('Subject ID'),
                        TextEntry::make('causer_type')
                            ->label('Actor Type'),
                        TextEntry::make('causer_id')
                            ->label('Actor ID'),
                        TextEntry::make('created_at')
                            ->label('Created At')
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
                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('properties')
                            ->label('Properties')
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                            ->copyable(),
                    ]),
            ]);
    }
}
