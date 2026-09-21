---
title: Version compatibility
description: Supported Filament, Laravel and PHP versions for Filament Page Header.
---

This documentation describes the **2.x** package line. Package major versions identify this package's API and do not correspond to Filament major versions.

| Package version | Filament requirement | PHP requirement | Laravel | API |
| --- | --- | --- | --- | --- |
| `^2.0` | `^4.12.6 || ^5.8.1` | `^8.3` | 12 or 13 | `Header`, `MetadataEntry`, typed compact configuration |
| `^1.0` | `^5.8.1` | `^8.3` | 12 or 13 | Previous `HeaderLayout` API |

Version 2 is tested against supported Filament 4 and 5 releases across Laravel 12 and 13. PHP must also satisfy the requirements of the selected Laravel version.

For the previous API, see the [1.x README](https://github.com/mortalkiller/filament-page-header/blob/1.x/README.md).

If you are upgrading an existing application, follow the [migration guide](../../guides/migration/).
