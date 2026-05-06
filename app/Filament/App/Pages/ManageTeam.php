<?php

namespace App\Filament\App\Pages;

use App\Filament\Widgets\TeamAttendanceWidget;
use App\Filament\Widgets\TeamPendingRequestsWidget;
use App\Filament\Widgets\TeamStatsOverview;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ManageTeam extends Page
{
    use HasPageShield;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected string $view = 'filament.app.pages.manage-team';

    protected static ?string $title = 'Gerir a minha equipa';

    protected static ?string $navigationLabel = 'Gerir Equipa';

    protected static \UnitEnum|string|null $navigationGroup = 'Gestão de Unidade';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            return false;
        }

        // Must have Shield permission AND be a manager (have managed units)
        return parent::canAccess() && $user->employee->getAllManagedUnits()->isNotEmpty();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TeamStatsOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TeamPendingRequestsWidget::class,
            TeamAttendanceWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return 1;
    }
}
