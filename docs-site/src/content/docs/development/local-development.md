---
title: Local package development
description: Develop Filament Page Header locally using Composer path repositories and a consuming application.
---

The application and the plugin remain separate Git repositories. This workflow does not require committing, pushing or tagging each change.

## Directory layout

```text
projects/
  printeemize/
  filament-page-header/
```

Work on a feature branch based on `2.x` in the plugin. Keep application integration changes on a separate branch in Pressiu. This workflow does not install integration automatically. Version 2 replaces the version 1 header API; migrate consuming headers before switching an existing v1 integration to this code. The package and application do not need matching branch names.

## Composer path repository

Merge the following into the **consuming application's** composer.json, keeping existing repositories and requirements. Put the path repository before a VCS repository for this same package.

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../filament-page-header",
            "options": {
                "symlink": true,
                "versions": {
                    "mortalkiller/filament-page-header": "2.x-dev"
                }
            }
        }
    ]
}
```

From the application directory:

```bash
composer require mortalkiller/filament-page-header:2.x-dev
php artisan filament:assets
```

The explicit development constraint applies only to this package. Keep the project's global stability unchanged. The path version override is a local Composer resolution setting, not a release or Git tag. Composer should report that the package was symlinked; do not edit a copied vendor directory and assume changes will return to the plugin repository.

Register `PageHeaderPlugin::make()` in the intended panel and use `HasPageHeader` on the intended page, as shown in the README.

## Docker

PHP and Composer need to see both directories at paths that make the symlink resolvable. Example container paths:

```text
/var/www/printee
/var/www/filament-page-header
```

Add a development bind mount for the plugin alongside the existing application mount. The following is an illustrative addition, not a replacement Compose configuration:

```yaml
services:
  php:
    volumes:
      - ../filament-page-header:/var/www/filament-page-header
```

Use the actual service name in your Compose project. PHP workers and any separate Composer container that resolves the package need compatible mounts. The web server serves published assets from the application's public directory; mount that directory consistently too.

Pressiu's documented local command convention is:

```bash
docker exec -u ubuntu -w /var/www/printee dockerworker composer require mortalkiller/filament-page-header:2.x-dev
docker exec -u ubuntu -w /var/www/printee dockerworker php artisan filament:assets
```

This is a consumer-specific example. The plugin itself does not depend on these names or paths. Do not mount a development package folder into production.

## Change and test

| Changed file | Local refresh |
| --- | --- |
| Existing PHP or Blade | Reload the page. The symlink points at the working files. |
| New class or autoload metadata | Run composer dump-autoload if optimized/classmap state needs refreshing. |
| Package CSS or JavaScript | Run php artisan filament:assets in the consuming app, then reload. |
| Package dependencies | Run composer update mortalkiller/filament-page-header in the consuming app and review the lock diff. |
| Cached views/configuration | Clear only the affected development caches. |
| Long-lived PHP processes or disabled OPcache timestamp validation | Restart the relevant local processes. |

The CSS and JavaScript are plain package assets and require no Vite build. Filament copies them into public; a Composer symlink alone does not update those copies. An optional development watcher can run filament:assets after resource changes. Do not run composer update for every PHP edit.

For independent testing, use the workbench and commands in [Demo and testing](testing.md). Do not substitute a successful Pressiu page render for the package's own tests.

## Return to a distributable dependency

A lock file resolved from a path repository records that local source. It must not accidentally be deployed to an environment without the path.

1. Remove the temporary path repository from the application's composer.json.
2. Restore the normal Packagist/VCS distribution source.
3. Require the published stable constraint, for example `^2.0`, or deliberately retain `2.x-dev` only while testing unreleased changes.
4. Run a targeted update for this package. Confirm its lock entry no longer uses a local path.
5. Verify composer install in a clean checkout without the sibling package directory and publish the assets there.
6. Commit the reviewed consumer configuration and lock file together.

Never publish a release merely to test a local edit. Do not commit credentials, generated workbench environment files, vendor or node_modules.

## Reference

Composer path repositories: https://getcomposer.org/doc/05-repositories.md#path
Filament assets: https://filamentphp.com/docs/5.x/advanced/assets

## Reuse an installed Chromium for local tests

When the environment already provides a suitable Chromium executable, set `PLAYWRIGHT_CHROMIUM_EXECUTABLE` to its actual executable path before running `npm run test:browser`. The override is optional and affects tests only; CI continues to use the browser installed by Playwright. A different Chromium revision is useful for local checks but does not replace the CI browser matrix.
