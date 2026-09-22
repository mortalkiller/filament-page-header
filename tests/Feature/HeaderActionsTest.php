<?php

declare(strict_types=1);

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

it('keeps the existing action defaults independently of compact content selection', function (): void {
    $header = Header::make()->whenCompact(fn (CompactHeader $compact) => $compact->show(HeaderPart::Metadata));
    $action = Action::make('save');

    expect($header->getActionsPosition())->toBe(HeaderActionsPosition::End)
        ->and($header->prepareHeaderActions([$action]))->toBe([$action])
        ->and($action->getExtraAttributes()['data-fph-action-compact'])->toBe('true');
});

it('supports typed action positions and runtime closures', function (): void {
    foreach (HeaderActionsPosition::cases() as $position) {
        expect(Header::make()->actionsPosition($position)->getActionsPosition())->toBe($position)
            ->and(Header::make()->actionsPosition(fn () => $position)->getActionsPosition())->toBe($position);
    }
});

it('selects native action names without reordering or replacing instances', function (): void {
    $save = Action::make('save')->label('Persist');
    $approve = Action::make('approve');
    $delete = Action::make('delete');
    $group = ActionGroup::make([$approve, $delete]);
    $actions = [$save, $group];
    $header = Header::make()->whenCompact(fn (CompactHeader $compact) => $compact->actions(['approve', 'save', 'save', 'unknown']));

    expect($header->prepareHeaderActions($actions))->toBe($actions)
        ->and($group->getFlatActions()['approve'])->toBe($approve)
        ->and($approve->getGroup())->toBe($group)
        ->and($save->getExtraAttributes()['data-fph-header-action'])->toBe('save')
        ->and($save->getExtraAttributes()['data-fph-action-compact'])->toBe('true')
        ->and($approve->getExtraAttributes()['data-fph-action-compact'])->toBe('true')
        ->and($delete->getExtraAttributes()['data-fph-action-compact'])->toBe('false');
});

it('uses the last action selection and distinguishes empty inclusion from exclusion', function (): void {
    $compact = new CompactHeader;
    expect($compact->isActionVisible('save'))->toBeTrue();
    $compact->actions([]);
    expect($compact->isActionVisible('save'))->toBeFalse();
    $compact->hideActions([]);
    expect($compact->isActionVisible('save'))->toBeTrue();
    $compact->actions(['save'])->hideActions(['delete']);
    expect($compact->isActionVisible('save'))->toBeTrue()
        ->and($compact->isActionVisible('approve'))->toBeTrue()
        ->and($compact->isActionVisible('delete'))->toBeFalse();
    $compact->actions(['approve']);
    expect($compact->isActionVisible('save'))->toBeFalse()
        ->and($compact->isActionVisible('approve'))->toBeTrue();
});

it('preserves native visibility authorization and disabled state', function (): void {
    $hidden = Action::make('hidden')->hidden();
    $denied = Action::make('denied')->authorize(false);
    $disabled = Action::make('disabled')->disabled();
    $header = Header::make()->whenCompact(fn (CompactHeader $compact) => $compact->actions(['hidden', 'denied', 'disabled']));
    $header->prepareHeaderActions([$hidden, $denied, $disabled]);

    expect($hidden->isVisible())->toBeFalse()
        ->and($denied->isAuthorized())->toBeFalse()
        ->and($disabled->isDisabled())->toBeTrue();
});

it('updates presentation idempotently without replacing consumer attribute closures', function (): void {
    $action = Action::make('save')->extraAttributes(fn () => ['data-consumer' => 'kept']);
    $header = Header::make()->whenCompact(fn (CompactHeader $compact) => $compact->actions([]));
    $header->prepareHeaderActions([$action]);
    $attributes = new ReflectionProperty(Action::class, 'extraAttributes');
    $attributeCount = count($attributes->getValue($action));
    $header->prepareHeaderActions([$action]);
    expect(count($attributes->getValue($action)))->toBe($attributeCount);
    $header->whenCompact(fn (CompactHeader $compact) => $compact->hideActions([]));
    $header->prepareHeaderActions([$action]);
    expect($action->getExtraAttributes())->toMatchArray([
        'data-consumer' => 'kept',
        'data-fph-header-action' => 'save',
        'data-fph-action-compact' => 'true',
    ]);
});

it('keeps selection isolated between headers and resets it with whenCompact', function (): void {
    $first = Header::make()->whenCompact(fn (CompactHeader $compact) => $compact->actions([]));
    $second = Header::make();
    $firstAction = Action::make('save');
    $secondAction = Action::make('save');
    $first->prepareHeaderActions([$firstAction]);
    $second->prepareHeaderActions([$secondAction]);
    expect($firstAction->getExtraAttributes()['data-fph-action-compact'])->toBe('false')
        ->and($secondAction->getExtraAttributes()['data-fph-action-compact'])->toBe('true');
    $first->whenCompact(function (CompactHeader $compact): void {});
    $first->prepareHeaderActions([$firstAction]);
    expect($firstAction->getExtraAttributes()['data-fph-action-compact'])->toBe('true');
});

it('rejects invalid action identifiers without changing the previous selection', function (mixed $name): void {
    $compact = (new CompactHeader)->actions(['save']);
    foreach (['actions', 'hideActions'] as $method) {
        expect(fn () => $compact->{$method}([$name]))->toThrow(InvalidArgumentException::class);
        expect($compact->isActionVisible('save'))->toBeTrue()
            ->and($compact->isActionVisible('delete'))->toBeFalse();
    }
})->with(['empty' => [''], 'blank' => ['  '], 'integer' => [123], 'null' => [null], 'array' => [[]]]);
