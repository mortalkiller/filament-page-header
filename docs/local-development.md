# Local package development

The consuming application and the plugin should remain separate Git repositories. This workflow lets you test unreleased package changes without publishing a release for every edit.

## Directory layout

The names below are deliberately generic examples:

```text
projects/
  demo-filament-app/
  filament-page-header/
```

Work on a feature branch based on `2.x` in the plugin. Keep any application integration changes on a separate branch in the consuming application. The package and application do not need matching branch names.

Version 2 replaces the version 1 header API, so migrate consuming headers before switching an existing v1 integration to unreleased `2.x` code.

## Composer path repository

Merge the following into the **consuming application's** `composer.json`, keeping any existing repositories and requirements. Put the path repository before a VCS repository for the same package.

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

From the consuming application directory:

```bash
composer require mortalkiller/filament-page-header:2.x-dev
php artisan filament:assets
```

The explicit development constraint applies only to this package. Keep the application's global stability unchanged. The path version override is a local Composer resolution setting, not a release or Git tag.

Composer should report that the package was symlinked. Do not edit a copied `vendor/` directory and assume those changes will return to the plugin repository.

Register `PageHeaderPlugin::make()` in the intended panel and use `HasPageHeader` on the intended pages.

## Docker

PHP and Composer need access to both repositories at paths that make the symlink resolvable.

Example container paths:

```text
/var/www/app
/var/www/filament-page-header
```

A generic Compose development setup could expose both folders to the PHP service:

```yaml
services:
  php:
    volumes:
      - ../demo-filament-app:/var/www/app
      - ../filament-page-header:/var/www/filament-page-header
```

Use the actual service names and paths from your own environment. Any PHP worker or Composer container that resolves the package needs compatible mounts.

With the illustrative service above, commands would look like:

```bash
docker compose exec -w /var/www/app php composer require mortalkiller/filament-page-header:2.x-dev
docker compose exec -w /var/www/app php php artisan filament:assets
```

The names `demo-filament-app`, `php`, and `/var/www/app` are examples only. Do not mount a development package directory into production.

## Change and test

| Changed file | Local refresh |
| --- | --- |
| Existing PHP or Blade | Reload the page. The symlink points at the working files. |
| New class or autoload metadata | Run `composer dump-autoload` if optimized/classmap state needs refreshing. |
| Package CSS or JavaScript | Run `php artisan filament:assets` in the consuming app, then reload. |
| Package dependencies | Run `composer update mortalkiller/filament-page-header` in the consuming app and review the lock diff. |
| Cached views/configuration | Clear only the affected development caches. |
| Long-lived PHP processes or disabled OPcache timestamp validation | Restart the relevant local processes. |

The CSS and JavaScript are plain package assets and require no Vite build. Filament copies them into the consuming application's public directory, so a Composer symlink alone does not refresh published assets.

For independent validation, use the package workbench and the commands in [Demo and testing](testing.md). A successful render inside one consuming application does not replace the package's own test suites.

## Return to a distributable dependency

A lock file resolved from a path repository records that local source. It must not accidentally be deployed to an environment where that path does not exist.

1. Remove the temporary path repository from the consuming application's `composer.json`.
2. Restore the normal Packagist/VCS distribution source.
3. Require the published stable constraint, for example `^2.0`, or deliberately retain `2.x-dev` only while testing unreleased changes.
4. Run a targeted update for this package and confirm the lock entry no longer uses a local path.
5. Verify `composer install` in a clean checkout without the sibling package directory and publish Filament assets there.
6. Commit the reviewed consumer configuration and lock file together.

Never publish a release merely to test a local edit. Do not commit credentials, generated workbench environment files, `vendor/`, or `node_modules/`.

## Reference

Composer path repositories: https://getcomposer.org/doc/05-repositories.md#path  
Filament assets: https://filamentphp.com/docs/5.x/advanced/assets

## Reuse an installed Chromium for local tests

When the environment already provides a suitable Chromium executable, set `PLAYWRIGHT_CHROMIUM_EXECUTABLE` to its actual executable path before running `npm run test:browser`.

The override is optional and affects local tests only; CI continues to use the browser installed by Playwright. A different Chromium revision is useful for local checks but does not replace the CI browser matrix.
