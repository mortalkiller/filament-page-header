---
title: Enums
description: HeaderMode, HeaderActionsPosition, HeaderPart, and BreadcrumbPosition enum cases.
---

The package uses enums for configuration where a fixed set of values is meaningful.

## HeaderMode

Namespace:

```php
MortalKiller\FilamentPageHeader\Enums\HeaderMode
```

| Case | Value | Behavior |
| --- | --- | --- |
| `HeaderMode::Normal` | `normal` | Header scrolls normally with the page. |
| `HeaderMode::Sticky` | `sticky` | Full header pins at the sticky edge. |
| `HeaderMode::Compact` | `compact` | Header compacts after reaching the sticky edge. |

Example:

```php
PageHeaderPlugin::make()
    ->mode(HeaderMode::Sticky);
```

## HeaderActionsPosition

Namespace:

```php
MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition
```

| Case | Value | Desktop behavior |
| --- | --- | --- |
| `HeaderActionsPosition::Start` | `start` | Action block before the main content, at the logical inline start. |
| `HeaderActionsPosition::End` | `end` | Action block after the main content; the default. |
| `HeaderActionsPosition::Below` | `below` | Action block on its own row after the header content, before sub-navigation. |

Start and End respect RTL. Mobile retains the full-width action area after the details for every position. These values position the block, not the order of its actions.

```php
Header::make()
    ->actionsPosition(HeaderActionsPosition::Below);
```

See [Native header actions](../../guides/native-actions/) for compact selection and interaction behavior.

## HeaderPart

Namespace:

```php
MortalKiller\FilamentPageHeader\Enums\HeaderPart
```

Used by [`CompactHeader`](../compact-header/). Native actions use their separate `actions()` / `hideActions()` policy, not a `HeaderPart` case.

| Case | Internal value | Represents |
| --- | --- | --- |
| `HeaderPart::Image` | `leading` | Avatar, product image, initials, icon, or custom leading block. |
| `HeaderPart::Description` | `description` | Description/subheading block. |
| `HeaderPart::Badges` | `badges` | Badge/status block. |
| `HeaderPart::Metadata` | `metadata` | Metadata fields. |
| `HeaderPart::Summary` | `summary` | Summary/totals block. |
| `HeaderPart::Content` | `default` | Additional default schema content. |
| `HeaderPart::Breadcrumbs` | `breadcrumbs` | Native breadcrumbs. |
| `HeaderPart::SubNavigation` | `subNavigation` | Native Page/Resource sub-navigation. |

Example:

```php
$compact->show(
    HeaderPart::Image,
    HeaderPart::Badges,
    HeaderPart::SubNavigation,
);
```

## BreadcrumbPosition

Namespace:

```php
MortalKiller\FilamentPageHeader\Enums\BreadcrumbPosition
```

| Case | Value | Behavior |
| --- | --- | --- |
| `BreadcrumbPosition::Outside` | `outside` | Render breadcrumbs above/outside the header card. |
| `BreadcrumbPosition::Inside` | `inside` | Render breadcrumbs inside the header. |
| `BreadcrumbPosition::Hidden` | `hidden` | Hide breadcrumbs for this header. |

Example:

```php
Header::make()
    ->breadcrumbs(BreadcrumbPosition::Inside);
```
