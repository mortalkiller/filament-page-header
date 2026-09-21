---
title: Filament Page Header
description: Build informative, responsive page headers with native Filament schemas.
template: splash
editUrl: false
hero:
  tagline: Responsive, native-first page headers for Filament 4 and 5.
  actions:
    - text: Get started
      link: /filament-page-header/getting-started/installation/
      icon: right-arrow
    - text: API reference
      link: /filament-page-header/api/
      variant: minimal
    - text: View on GitHub
      link: https://github.com/mortalkiller/filament-page-header
      icon: external
      variant: minimal
---

Filament Page Header lets you combine identity, status, metadata, summary information and native page actions in a responsive header while keeping Filament's own schemas, actions and authorization behavior.

![Filament Page Header example in dark mode](/filament-page-header/filament-page-header-dark-2560x1440.jpg)

## Highlights

- Native Filament schema components and page actions.
- Normal, sticky and compact layouts.
- Responsive behavior for desktop and mobile.
- Shared resource header schemas with page-level overrides.
- Images, avatars, initials, icons, badges, metadata and summaries.
- Native breadcrumbs and page/record sub-navigation.
- Artisan generator for conventional Resource header setup.
- Light and dark mode support without a custom theme build.

## Start here

Install the package and register it only on the panels where custom headers should be available. Then add `HasPageHeader` to the pages that should opt in.

The [installation guide](/filament-page-header/getting-started/installation/) walks through the complete first setup. When you need the exact fluent API, use the [API reference](/filament-page-header/api/).
