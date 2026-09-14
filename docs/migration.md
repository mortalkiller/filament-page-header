# Migration from v1

- Replace `HeaderLayout` with `Header`.
- Replace `subheading()` with `description()` and `trailing()` with `summary()`.
- Pass metadata and metrics directly; remove Grids used only to arrange these standard regions.
- Replace manual photo/initials composition with `avatar()` and `initials()` where appropriate.
- Prefer `sticky()->compactBelow(1024)` over the equivalent panel breakpoint map.
- Prefer `whenCompact()` with `CompactHeader` and `HeaderPart` to select the blocks and fields that remain visible when compact.
- Publish assets again; PHP/Blade symlinks do not update published CSS/JS.

Version 2 introduces breaking API changes. Update the dependency to `^2.0`, migrate consuming schemas and test the application together before deployment. The previous `HeaderLayout` API remains on the `1.x` line.

## Deprecated compact configuration

`hideWhenCompact()` and `retainSummaryWhenCompact()` remain deprecated compatibility methods. New configuration should use `whenCompact()`; do not mix the APIs. If mixed, the last configuration method selects the active API. Existing per-component `data-fph-hide-compact` attributes remain supported for legacy views.

Breadcrumbs render above and outside the header card, and scroll with the page. They never occupy the pinned header. The former `HeaderOptions::hideBreadcrumbsWhenCompact()` option remains readable for compatibility with custom views, but has no effect on the package view.

[Back to the README](../README.md)
