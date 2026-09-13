# Filament Page Header

Opt-in, schema-based page headers for **Filament 5**. Combine headings, badges, avatars, metadata, links, native actions and a trailing summary. Optionally keep the header visible while scrolling, either at full size or in a compact layout.

The package has no dependency on a consuming application's models, theme, database, billing rules or icon package. Installing it does not replace every page header.

## Status and requirements

The maintained stable line is **1.x**. Stable versions are published through Packagist from Git tags such as `v1.0.0`. See [the verification record](docs/verification.md) for executed checks and remaining manual checks.

- PHP 8.3 or later within PHP 8.
- Filament 5.8.1 or later within Filament 5.
- Laravel 12 or 13, subject to the framework's PHP requirements.
- A browser supporting CSS sticky positioning, ResizeObserver and MutationObserver.

## Installation

Install the stable 1.x line from Packagist:

```bash
composer require mortalkiller/filament-page-header:^1.0
php artisan filament:assets
```

For unreleased work on the maintained branch, use `1.x-dev` deliberately. Do not change the entire application's `minimum-stability` to `dev`. Keep this dependency in `require`, not `require-dev`, when the application uses its headers at runtime. Review and commit the application's lock file deliberately.

The service provider is auto-discovered. Enable the plugin separately on each panel:

```php
use Filament\Panel;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;

public function panel(Panel $panel): Panel
{
    return $panel->plugin(PageHeaderPlugin::make());
}
```

## Configure a page

Use the trait on a resource Create, Edit, View or List page, or a custom Filament page. All content remains a normal Filament schema:

```php
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use MortalKiller\FilamentPageHeader\Components\HeaderLayout;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

use HasPageHeader;

public function headerSchema(Schema $schema): Schema
{
    return $schema->components([
        HeaderLayout::make()
            ->badges([
                TextEntry::make('status')
                    ->state(fn (?Model $record) => $record?->getAttribute('status') ?? __('New'))
                    ->badge()
                    ->hiddenLabel(),
            ])
            ->metadata([
                TextEntry::make('reference')->hiddenLabel(),
            ]),
    ]);
}
```

Place the trait declaration and method inside your page class. `HeaderLayout::make()` inherits the page's heading and subheading. It does not change the browser tab title. Create and List pages do not require a persisted record.

### Layout slots

| Method | Purpose |
| --- | --- |
| `heading($state, html: false)` | Page heading; accepts a literal value or closure. |
| `headingSchema([...])` | Custom heading composition, including the package's `Heading` component. |
| `subheading($state, html: false)` | Supporting text. |
| `badges([...])` | Ordered native entries, including `TextEntry::badge()` and enums. |
| `leading([...])` | Optional icon, avatar or logo. Use native entries; the package does not manage uploads. |
| `metadata([...])` | References, dates, links and other secondary information. |
| `trailing([...])` | Optional summary or total, separate from the page's native actions. |
| `schema([...])` | Additional components or native actions below the title. |
| `hideWhenCompact([...])` | Replace the list of slots hidden in compact mode. |

Slot methods accept arrays or closures. Use normal Filament visibility, colors, icons and authorization APIs. Do not put persistence, API requests or expensive calculations inside rendering closures.

A page should render one principal `h1`. `HeaderLayout` supplies one by default; preserve that rule when supplying `headingSchema()`. Optional empty slots are omitted. Badges wrap instead of forcing page-wide horizontal scrolling.

Native `getHeaderActions()` remains independent. Existing `formId`, ActionGroup, modal, confirmation and authorization behavior is retained. An action in the schema is additional content, not a replacement for native header actions.

### Images and icons

```php
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Icons\Heroicon;
use MortalKiller\FilamentPageHeader\Components\Heading;

$layout
    ->headingSchema([
        Heading::make('title')->state(__('Customers'))->icon(Heroicon::OutlinedUsers),
    ])
    ->leading([
        ImageEntry::make('avatar_url')
            ->defaultImageUrl(asset('images/avatar-fallback.svg'))
            ->circular()
            ->imageHeight(56)
            ->hiddenLabel()
            ->extraImgAttributes(['alt' => __('Customer avatar')]),
    ]);
```

Supply fallback assets and meaningful alternative text in the consuming application. No specific icon library is required. The leading region limits image dimensions without distorting its proportions.

### Share configuration across a resource

Define a class such as `App\Filament\Resources\Orders\Schemas\OrderHeader` with `public static function configure(Schema $schema): Schema`. The trait discovers `{Model}Header` beside the resource by convention, including parent resource namespaces.

Alternatively, map the class explicitly on the panel plugin:

```php
PageHeaderPlugin::make()->schemaFor(OrderResource::class, OrderHeader::class);
```

An inline `headerSchema()` method overrides discovery. Add the trait only to pages that should use the shared schema. A page without a schema, or with an empty schema, uses the native header. A `getHeader()` method defined on the page itself keeps precedence. Without panel activation, the trait falls back to the native header.

## Sticky and compact modes

```php
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;

PageHeaderPlugin::make()
    ->mode(HeaderMode::Normal)
    ->responsive([
        0 => HeaderMode::Compact,
        1024 => HeaderMode::Sticky,
    ]);
```

- `Normal` is the default and scrolls with the page.
- `Sticky` retains the complete header at the useful top edge.
- `Compact` retains the heading, essential badges and actions while reducing secondary content. Returning to the top restores the complete layout.

Breakpoint keys are minimum viewport widths in CSS pixels; the highest matching width wins. Customize one page without mutating the panel's defaults:

```php
use MortalKiller\FilamentPageHeader\HeaderOptions;

public function pageHeaderOptions(HeaderOptions $defaults): HeaderOptions
{
    return $defaults
        ->mode(HeaderMode::Compact)
        ->responsive([0 => HeaderMode::Compact, 1024 => HeaderMode::Sticky]);
}
```

`HeaderOptions` is immutable: keep the returned instance when configuring it. The plugin's fluent methods manage that for you.

### Offset and compact content

The default offset is derived from visible native topbars. Set an explicit pixel offset, or customize the selector for a different layout:

```php
PageHeaderPlugin::make()->offset(80);
PageHeaderPlugin::make()->topbarSelector('.my-topbar, .my-announcement-bar');
```

Passing `offset(null)` restores automatic detection; `topbarSelector(null)` disables topbar detection. Sticky positioning is bounded by the real scroll container and its ancestors. Avoid short wrappers or unintended `overflow: hidden` ancestors that prevent CSS sticky from reaching the page content.

By default, `subheading`, `metadata` and the additional `default` slot collapse. Badges, the trailing summary and native header actions remain. To keep inline schema actions visible:

```php
HeaderLayout::make()->hideWhenCompact(['subheading', 'metadata']);
```

Available hideable slots are `leading`, `subheading`, `metadata`, `default`, `trailing` and `badges`. The heading cannot be hidden. For finer control, mark an individual native component with `extraAttributes(['data-fph-hide-compact' => 'true'])`; do not also hide its entire parent slot. Keep critical warnings visible. Focused content is not hidden until focus leaves it.

Breadcrumbs collapse by default. Override that through `HeaderOptions::hideBreadcrumbsWhenCompact(false)`.

The browser handles scrolling and compaction without Livewire requests or duplicated action instances. A stable expanded footprint prevents layout jumps. On very short viewports or unusually tall headers, pinning is temporarily disabled so the page remains usable. Reduced-motion preferences are respected.

External job/provider changes are not polled by this package. The application must refresh its data through its existing Livewire events or refresh mechanisms.

## HTML and security

Headings and subheadings are escaped by default, including `HtmlString` values. Use `html: true` or `Heading::html()` only for trusted markup that your application has prepared. Escape untrusted values before including them in that markup. Native entries and actions retain their own security behavior; hiding an action is not authorization.

The package does not load arbitrary URLs on the server, persist settings or register demo routes in a consuming application. Assets are namespaced and loaded when the header needs them. No custom Tailwind theme or build step is required; publish assets using Filament's standard command.

## Independent demo and tests

From this repository:

```bash
composer install
php bin/prepare-workbench.php
php workbench/artisan package:discover
php workbench/artisan filament:assets
php workbench/artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000/demo/headers`. The workbench demonstrates ten compositions and all three scroll modes. It is a **local testing application**, not a production application; do not expose it publicly. It does not need Pressiu's database or theme.

Run checks from the repository root:

```bash
composer test
node --test tests/JavaScript/*.test.mjs
npm install
npx playwright install chromium
npm run test:browser
```

Playwright starts the local workbench automatically. Browser reports and screenshots are also retained as CI artifacts. PHP tests use an isolated in-memory database; browser tests use the separate workbench.

## Develop locally inside Pressiu or another application

See [Local development](docs/local-development.md) for Composer `path` + symlink configuration, Docker mounts, asset refresh and safe transition back to a distributed version. PHP/Blade changes can be tested without a commit, push, tag or release. Asset publishing is a local operation, not a release.

## Scope and credits

This version intentionally excludes visual editors, uploads, database-stored configuration, automatic polling and Filament 4 compatibility. Pressiu integration is a separate change; the package does not import Pressiu classes.

Inspired by [Vitis Studio's Filament Header Schema](https://github.com/VitisStudio/filament-header-schema), and built on native [Filament plugin and schema APIs](https://filamentphp.com/docs/5.x/plugins/panel-plugins). See [third-party notices](THIRD_PARTY_NOTICES.md).

Licensed under the [MIT license](LICENSE.md).
