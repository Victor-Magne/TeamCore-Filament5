<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Pages\UnitsTree;
use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUnit extends EditRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->successRedirectUrl(UnitsTree::getUrl()),
            ForceDeleteAction::make()
                ->successRedirectUrl(UnitsTree::getUrl()),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return UnitsTree::getUrl();
    }

    public function getBreadcrumbs(): array
    {
        return [
            UnitsTree::getUrl() => 'Unidades',
            $this->record->name,
        ];
    }
}
