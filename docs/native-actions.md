# Native header actions

Continue declaring actions in the page's native `getHeaderActions()`. The package changes their presentation, not their registration, handlers, authorization, form targets or modal lifecycle.

## Position the action block

```php
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition;

Header::make()->actionsPosition(HeaderActionsPosition::End);
Header::make()->actionsPosition(HeaderActionsPosition::Start);
Header::make()->actionsPosition(HeaderActionsPosition::Below);
```

`End` is the default: the block follows the main identity/content row. `Start` places the block before that content at the logical inline start. Both respect RTL direction. `Below` puts the block on its own row after the header content and before any header sub-navigation. Positions may wrap when space is limited.

On mobile, all positions retain the existing full-width action area after the details. Position refers to the block, not to the order of actions inside it. `actionsPosition()` also accepts a closure returning `HeaderActionsPosition`.

## Select actions in compact mode

Assuming the page already declares actions named `save` and `approve`:

```php
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition;

Header::make()
    ->actionsPosition(HeaderActionsPosition::End)
    ->compact()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->actions(['save', 'approve']));
```

Use native action names, not translated labels or group labels. Selection is recursive inside native groups and preserves the original action order and group hierarchy. It does not promote a dropdown item to a standalone button.

Alternatively, exclude a few actions:

```php
Header::make()
    ->compact()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->hideActions(['delete', 'duplicate']));
```

| Configuration | Compact presentation |
| --- | --- |
| No action selection | Keep all actions allowed by Filament. |
| `actions(['save'])` | Keep only `save`, if available. |
| `actions([])` | Hide all native actions. |
| `hideActions(['delete'])` | Keep all except `delete`. |
| `hideActions([])` | Keep all. |

The last `actions()` or `hideActions()` call replaces the previous action policy. This policy is independent of `show()` and `only()` for other header parts. As before, a new `whenCompact()` call replaces the entire compact configuration, including the action selection.

Duplicate names are normalized. Unknown names are ignored so a shared header can support pages with different native actions. Empty or non-string names throw `InvalidArgumentException` without changing the previous selection.

These rules apply only while the header is actually compact. They do not filter normal, sticky or expanded presentation, and `whenCompact()` does not enable compact mode by itself.

## Native behavior and interaction

Native visibility, authorization and disabled state remain authoritative. Selecting an unauthorized or hidden action never exposes it. **Compact selection is not an authorization boundary:** enforce permission checks through Filament's server-side APIs.

Actions render once. The original cached instances, form targets, confirmation dialogs and modal forms remain in use. Scrolling does not issue Livewire requests or recreate the action tree.

Empty dropdowns, button groups and dropdown sections are removed from the compact layout. Header action render hooks are not filtered. Teleported dropdown panels are associated with their original header and are cleaned up on SPA navigation.

A focused action is retained until focus leaves it. A dropdown already open when compaction begins can finish its interaction before its items are filtered; normal selection resumes after it closes. An open modal remains available even when its original trigger is excluded from the compact presentation.

Custom action views must preserve the action's extra attributes in their output to participate in selection. Standard native buttons, links, dropdowns, nested groups and button groups do not need any changes.

## Updating and testing

Republish assets after updating the package:

```bash
php artisan filament:assets
```

The independent workbench includes `/demo/action-control`. Its query parameters include `position=start|end|below`, `selection=include|exclude|none|default|approve|save`, `teleport=1`, `simple=1` and `mode=normal|sticky|compact`. `action_hooks=1` enables a synthetic scoped render-hook example on the initial page request.

Configuration tests live in `tests/Feature/HeaderActionsTest.php`; browser regressions live in `tests/Browser/header-actions.spec.mjs`.
