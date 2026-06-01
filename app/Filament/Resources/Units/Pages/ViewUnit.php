<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Pages\UnitsTree;
use App\Filament\Resources\Units\UnitResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()
                ->successRedirectUrl(UnitsTree::getUrl()),
            ForceDeleteAction::make()
                ->successRedirectUrl(UnitsTree::getUrl()),
            RestoreAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            UnitsTree::getUrl() => 'Unidades',
            $this->record->name,
        ];
    }
}
