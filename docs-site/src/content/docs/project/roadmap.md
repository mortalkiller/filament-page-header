---
title: Roadmap
description: Current development direction and design principles for Filament Page Header.
---

This roadmap describes the current direction for Filament Page Header. It is intentionally focused on practical Filament workflows rather than turning the package into a general-purpose UI framework.

Planned versions and scope are directional and may change based on implementation findings, Filament changes, and community feedback.

## 2.2 — Developer experience

Reduce the setup needed to introduce page headers into an existing Filament resource.

Planned:

- Add a `make:filament-page-header` Artisan command.
- Accept a resource as the primary target.
- Support interactive page selection for List, Create, View, Edit, and custom resource pages where practical.
- Support non-interactive options for automation.
- Generate the conventional resource header schema.
- Add `HasPageHeader` safely to selected pages.
- Support panel selection when the application contains multiple panels.
- Make repeated execution safe and predictable.
- Provide `--force` and generation-only workflows where appropriate.

The generator should produce the same structure a developer would reasonably write by hand. It must not introduce a second configuration system.

## 2.3 — Header navigation

Make native Filament navigation concepts easier to compose with the page header.

Planned:

- Configurable native breadcrumb placement.
- Support breadcrumbs outside the header, inside the header, or hidden.
- Integrate native Filament record sub-navigation where available.
- Allow breadcrumb and sub-navigation visibility to participate in compact-mode configuration.
- Preserve Filament routing, authorization, active-state handling, and generated URLs.

The package should render existing Filament navigation rather than implement its own router or navigation state.

## 2.4 — Action control

Give applications more control over how native page actions behave inside responsive and compact headers.

Planned:

- Configurable desktop action positioning.
- Keep the existing mobile-friendly full-width behavior as a supported default.
- Allow selected native actions to remain visible in compact mode.
- Allow selected native actions to be hidden in compact mode.
- Preserve native Filament actions, modals, authorization, forms, button groups, and hooks.

The package must not duplicate or replace Filament's action system.

## Documentation and context improvements

Before adding more API, improve discoverability for capabilities the package already has.

Planned:

- Document `getPageHeaderRecord()` for tenant-backed pages.
- Document parent-record headers.
- Document settings/singleton pages.
- Document custom pages that use a model or array as header context.
- Add complete examples for applications with multiple panels.

## Design principles

New features should follow these rules:

- **Native first.** Reuse Filament schemas, actions, navigation, colors, hooks, and authorization whenever possible.
- **Opt in.** Installing the package must not unexpectedly replace native Filament behavior.
- **No custom theme requirement.** Consumers should not need to rebuild a Tailwind theme to use the package.
- **Small public API.** Prefer a few predictable Laravel-style methods over many visual configuration flags.
- **Progressive configuration.** Simple use cases should stay simple; advanced configuration should build naturally on the basic API.
- **Responsive by default.** Desktop, mobile, sticky, compact, light, and dark behavior must be considered as part of feature design.
- **Regression tested.** New layout behavior requires PHP and/or browser coverage appropriate to the risk.
- **Backwards compatible when practical.** Breaking API changes should be reserved for major releases.

## Out of scope

The package is not intended to become:

- A general Filament page builder.
- A replacement for Filament actions.
- A replacement for Filament navigation or routing.
- A notification system.
- A generic design-token or theme framework.
- A collection of arbitrary shadows, gradients, spacing presets, and visual variants.
- A record previous/next navigation package.

Features outside the page-header responsibility should stay in Filament, the consuming application, or focused third-party packages.

## Proposing roadmap changes

Concrete bugs and feature proposals should be tracked in GitHub Issues before implementation.

For significant features, the issue should describe:

- The user problem being solved.
- A proposed public API or usage example.
- Alternatives considered.
- Explicit non-goals.
- Compatibility considerations.
- Acceptance criteria.
- Required tests and documentation.

Implementation pull requests should reference the corresponding issue and remain focused on one roadmap item.
