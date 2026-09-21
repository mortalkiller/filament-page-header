<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition;
use MortalKiller\FilamentPageHeader\Support\HeaderActionsPresenter;

trait HasHeaderActions
{
    protected HeaderActionsPosition|Closure $actionsPosition = HeaderActionsPosition::End;

    public function actionsPosition(HeaderActionsPosition|Closure $position): static
    {
        $this->actionsPosition = $position;

        return $this;
    }

    public function getActionsPosition(): HeaderActionsPosition
    {
        return $this->evaluate($this->actionsPosition);
    }

    /**
     * @internal Decorate the native tree without replacing, regrouping or filtering actions.
     *
     * @param  array<Action|ActionGroup>  $actions
     * @return array<Action|ActionGroup>
     */
    public function prepareHeaderActions(array $actions): array
    {
        return HeaderActionsPresenter::prepare($actions, $this->compactContent);
    }
}
