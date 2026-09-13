# Filament Page Header specification

Approved scope: Trello card https://trello.com/c/2FVdqTJt and its functional checklist.

## Boundary
An independent Composer package for Filament 5; no consumer App classes, application database, tenant identifiers, fiscal logic or required icon pack. The Pressiu integration belongs to https://trello.com/c/YJLS8PS1 and is not part of this change. Do not publish releases or merge main automatically.

## Public behavior
Opt-in per panel and page. Shared resource schema with explicit override. Native heading fallback for absent/empty schema; a page's own getHeader wins. Null records on Create/List/custom pages are valid. Native actions, action groups, modal lifecycle, formId, breadcrumbs and four heading/action render hooks must survive. Browser title is independent.

Schema content supports titles, subtitles, ordered native badges/enums, optional icon/avatar/logo with fallback, metadata and links, trailing summaries, and additional actions. Empty content leaves no separators. Escape text; trusted HTML requires explicit opt-in. The layout has leading, heading/badges, metadata/body and trailing slots. Use the native theme, no consumer Tailwind build requirement.

Normal mode is default. Optional sticky and sticky-compact modes, responsive minimum-width overrides and automatic/configurable topbar offset. Compact mode retains heading, essential badges and actions; applications control secondary slots. One DOM instance, no duplicate actions, layout jumps or network calls on scroll. Keyboard focus, menus, modals, long headings, sidebar resizing, repeated SPA navigation and observer cleanup matter. No automatic polling or persistence.

## Verification
Ten demo variants: basic, single badge, multiple badges, icon, avatar/logo, metadata/links, trailing total, inline actions, creation without a record, and responsive composition. Exercise normal/sticky/compact modes. PHP tests cover configuration, fallback, schemas, records, enums, escaping, actions and hooks. Browser tests cover widths 360/390/768/1024 and desktop, light/dark, scroll transitions, SPA, preserved inputs and zero scroll requests. Document limitations of emulated testing versus physical devices.

Independent workbench and Composer path/symlink documentation required. No visual editor, database configuration, uploads, generator, automatic polling or other Filament major versions.
