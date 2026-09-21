# Roadmap

This roadmap describes future functionality being considered for Filament Page Header. It is intentionally focused on practical Filament workflows rather than turning the package into a general-purpose UI framework.

Items are directional and may change based on implementation findings, Filament changes, and community feedback.

## Action control

Give applications more control over how native page actions behave inside responsive and compact headers.

Planned:

- Configurable desktop action positioning.
- Allow selected native page actions to remain visible in compact mode.
- Allow selected native page actions to be hidden in compact mode.

Any implementation must continue to use Filament's native action system and preserve authorization, modals, forms, button groups, hooks, and action state. The package must not duplicate or replace Filament actions.

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
