<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class ListUnits extends Page
{
    protected static string $resource = UnitResource::class;

    protected string $view = 'filament.resources.units.pages.list-units';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Criar Unidade')
                ->url(UnitResource::getUrl('create'))
                ->icon(Heroicon::Plus)
                ->color('primary'),
        ];
    }

    public function getRoots(): Collection
    {
        return Unit::query()
            ->with([
                'managers',
                'children' => fn ($q) => $q->orderBy('name')
                    ->with([
                        'managers',
                        'children' => fn ($q) => $q->orderBy('name')
                            ->with('managers')
                            ->withCount('employees'),
                    ])
                    ->withCount('employees'),
            ])
            ->withCount('employees')
            ->whereNull('parent_id')
            ->orderByDesc('is_main_direction')
            ->orderBy('name')
            ->get();
    }
}
