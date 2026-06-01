<?php

namespace App\Filament\Pages;

use App\Models\Unit;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Unique;
use Kalnoy\Nestedset\NestedSet;
use UnitEnum;
use Wsmallnews\FilamentNestedset\Pages\NestedsetPage;

class UnitsTree extends NestedsetPage
{
    // Required by HasUnsavedDataChangesAlert used in NestedsetPage
    public array $data = [];

    protected static ?string $model = Unit::class;

    protected static ?string $modelLabel = 'Unidade';

    protected static string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Swatch;

    protected static string|UnitEnum|null $navigationGroup = 'Recursos Humanos';

    protected static ?string $navigationLabel = 'Unidades';

    protected static ?string $title = 'Organograma de Unidades';

    protected static ?int $navigationSort = 1;

    protected static ?int $level = null;

    protected function schema(array $arguments): array
    {
        return [
            TextInput::make('name')
                ->label('Nome da Unidade')
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label('Tipo')
                ->options([
                    'direction' => 'Direção',
                    'management' => 'Gestão',
                    'department' => 'Departamento',
                    'section' => 'Secção',
                ])
                ->required()
                ->native(false),

            Toggle::make('is_main_direction')
                ->label('É a Direção Principal?')
                ->default(false)
                ->hidden(function (?Unit $record) {
                    $exists = Unit::where('is_main_direction', true)->exists();
                    if (! $record) {
                        return $exists;
                    }

                    return $exists && ! $record->is_main_direction;
                })
                ->unique(
                    table: 'organizational_units',
                    column: 'is_main_direction',
                    ignorable: fn ($record) => $record,
                    modifyRuleUsing: fn (Unique $rule) => $rule->where('is_main_direction', 1)
                ),

            Textarea::make('description')
                ->label('Descrição')
                ->default(null),

            Select::make('managers')
                ->label('Gestores Responsáveis')
                ->multiple()
                ->relationship('managers', 'first_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                ->searchable()
                ->preload()
                ->helperText('Pode selecionar um ou mais gestores para esta unidade.'),
        ];
    }

    public function moveNodeAction(): Action
    {
        return Action::make('moveNode')
            ->label(__('sn-filament-nestedset::nestedset.action.move_node'))
            ->action(function (Action $action, array $arguments) {
                $id = $arguments['id'] ?? 0;
                $parent = ! isset($arguments['parent']) || empty($arguments['parent']) ? null : $arguments['parent'];
                $from = $arguments['from'] ?? 0;
                $to = $arguments['to'] ?? 0;

                $node = $this->getQuery()->findOrFail($id);

                if ($parent == $node->getAttribute(NestedSet::PARENT_ID)) {
                    if ($from == $to) {
                        return;
                    }
                    $shift = $from - $to;
                    $shift > 0 ? $node->up($shift) : $node->down(abs($shift));
                } else {
                    if (is_null($parent)) {
                        $node->saveAsRoot();
                        $siblingsCount = $node->refresh()->siblings()->count();
                        $node->up($siblingsCount - $to);
                    } else {
                        $parentNode = $this->getQuery()->withDepth()->findOrFail($parent);
                        $level = $this->getLevel();

                        if (! is_null($level) && $parentNode->depth >= $level - 1) {
                            Notification::make()
                                ->danger()
                                ->title(__('sn-filament-nestedset::nestedset.action.move_node_failed'))
                                ->body(__('sn-filament-nestedset::nestedset.action.move_node_failed_body_depth', ['level' => $level]))
                                ->send();

                            $action->cancel();
                            $action->halt();

                            return;
                        }

                        $invalidParentTypes = [
                            'department' => ['department', 'section'],
                            'section' => ['section'],
                        ];

                        $nodeType = $node->type;
                        $parentType = $parentNode->type;

                        if (isset($invalidParentTypes[$nodeType]) && in_array($parentType, $invalidParentTypes[$nodeType])) {
                            $typeLabels = [
                                'direction' => 'Direção',
                                'management' => 'Gestão',
                                'department' => 'Departamento',
                                'section' => 'Secção',
                            ];

                            Notification::make()
                                ->danger()
                                ->title('Movimento não permitido')
                                ->body("Um {$typeLabels[$nodeType]} não pode ser colocado dentro de um {$typeLabels[$parentType]}.")
                                ->send();

                            $action->cancel();
                            $action->halt();

                            return;
                        }

                        $parentNode->prependNode($node);
                        if ($to > 0) {
                            $node->down($to);
                        }
                    }
                }

                Notification::make()
                    ->success()
                    ->title(__('sn-filament-nestedset::nestedset.action.move_node_success'))
                    ->send();

                $action->success();
            })
            ->color('danger');
    }

    public function infolistSchema(): array
    {
        return [
            TextEntry::make('type')
                ->label('Tipo')
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'direction' => 'Direção',
                    'management' => 'Gestão',
                    'department' => 'Departamento',
                    'section' => 'Secção',
                    default => $state,
                }),

            TextEntry::make('managers.first_name')
                ->label('Gestores')
                ->badge()
                ->color('gray')
                ->placeholder('—'),

            IconEntry::make('is_main_direction')
                ->label('Principal')
                ->boolean(),
        ];
    }
}
