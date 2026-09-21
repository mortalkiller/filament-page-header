<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Support;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use MortalKiller\FilamentPageHeader\CompactHeader;
use stdClass;
use WeakMap;

/** @internal Presentation metadata only; Filament owns visibility and authorization. */
final class HeaderActionsPresenter
{
    /** @var WeakMap<Action, stdClass>|null */
    private static ?WeakMap $attributes = null;

    /**
     * @param  array<Action|ActionGroup>  $actions
     * @return array<Action|ActionGroup>
     */
    public static function prepare(array $actions, ?CompactHeader $compact): array
    {
        self::$attributes ??= new WeakMap;

        foreach ($actions as $item) {
            foreach ($item instanceof ActionGroup ? $item->getFlatActions() : [$item] as $action) {
                if (! isset(self::$attributes[$action])) {
                    $attributes = (object) ['values' => []];
                    self::$attributes[$action] = $attributes;
                    // Capture metadata, never the action or page, so the weak key can be collected.
                    $action->extraAttributes(static fn (): array => $attributes->values, merge: true);
                }

                self::$attributes[$action]->values = [
                    'data-fph-header-action' => $action->getName(),
                    'data-fph-action-compact' => ($compact?->isActionVisible($action->getName()) ?? true) ? 'true' : 'false',
                ];
            }
        }

        return $actions;
    }
}
