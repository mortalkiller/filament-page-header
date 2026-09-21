---
title: Enums
description: HeaderMode, HeaderPart, and BreadcrumbPosition enum cases.
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

## HeaderPart

Namespace:

```php
MortalKiller\FilamentPageHeader\Enums\HeaderPart
```

Used by [`CompactHeader`](../compact-header/).

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
