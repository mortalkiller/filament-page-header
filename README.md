![Filament Page Header showcase](docs/Filament%20Page%20Header%20Showcase.png)

# Filament Page Header

Opt-in, schema-based page headers for **Filament 5**. Combine headings, badges, avatars, metadata, links, native actions and a summary. Optionally keep the header visible while scrolling, either at full size or in a compact layout.

The package has no dependency on a consuming application's models, theme, database, billing rules or icon package. Installing it does not replace every page header.

## Status and requirements

**This checkout contains an unreleased, breaking API redesign.** The examples below require this checkout; published 1.x releases use the former API. A major release is required before distributing these changes as a stable version. No release is made by this change. See [the verification record](docs/verification.md) for executed checks and remaining manual checks.

- PHP 8.3 or later within PHP 8.
- Filament 5.8.1 or later within Filament 5.
- Laravel 12 or 13, subject to the framework's PHP requirements.
- A browser supporting CSS sticky positioning, ResizeObserver and MutationObserver.

## Installation

The published stable line can be installed with the command below, but does not yet provide the redesigned API shown here. For this implementation use the local path workflow in [Local development](docs/local-development.md):

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
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

use HasPageHeader;

public function headerSchema(Schema $schema): Schema
{
    return $schema->components([
        Header::make()
            ->heading(fn (?Model $record) => $record?->getAttribute('name') ?? __('New customer'))
            ->description(fn (?Model $record) => $record?->getAttribute('email'))
            ->initials(fn (?Model $record) => $record?->getAttribute('name'))
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

Place the trait declaration and method inside your page class. `Header::make()` inherits the page's heading and subheading. It does not change the browser tab title. Create and List pages do not require a persisted record.

### Layout slots

| Method | Purpose |
| --- | --- |
| `heading($state, html: false)` | Page heading; accepts a literal value or closure. |
| `headingSchema([...])` | Custom heading composition, including the package's `Heading` component. |
| `description($state, html: false)` | Supporting text. |
| `descriptionSchema([...])` | Native supporting content, such as a copyable product code. |
| `badges([...])` | Ordered native entries, including `TextEntry::badge()` and enums. |
| `avatar($urlOrImageEntry)` | Image URL or native `ImageEntry`, displayed as an avatar. |
| `image($urlOrImageEntry)` | Square image with soft corners and contain fitting, suitable for products without cropping. |
| `initials($name)` | Full name used to generate initials when no image is rendered. |
| `leading([...])` | Optional icon, avatar or logo. Use native entries; the package does not manage uploads. |
| `metadata([...])` | References, dates, links and other secondary information. |
| `summary([...])` | Optional summary or total, separate from the page's native actions. |
| `schema([...])` | Additional components or native actions below the title. |
| `whenCompact(fn (CompactHeader $compact) => ...)` | Select compact blocks and fields using HeaderPart enums. |

Slot methods accept arrays or closures. Badge entry labels are hidden automatically. Metadata and summary arrange native components without requiring a Grid; use explicit native layouts only for custom compositions. Use normal Filament visibility, colors, icons and authorization APIs. Do not put persistence, API requests or expensive calculations inside rendering closures.

A page should render one principal `h1`. `Header` supplies one by default; preserve that rule when supplying `headingSchema()`. Optional empty slots are omitted. Badges wrap instead of forcing page-wide horizontal scrolling.

Native `getHeaderActions()` remains independent. Its actions render once beside the identity, right-aligned, and wrap to right-aligned rows when space runs out. On mobile, metadata and summary precede a single column of full-width actions. Native button groups keep their segments on one row, and standalone icon-only menu triggers are centered below the main actions. The first top-level Header hosts the native actions; arbitrary schemas without a Header keep an independent native action group. Existing `formId`, ActionGroup, modal, confirmation and authorization behavior is retained. An action in the schema is additional content, not a replacement for native header actions.

### Metadata separators and field icons

Direct metadata fields are separated by subtle vertical lines in both themes. A 32 px gap reserves 16 px on each side of the separator, whether the field has an icon or only text. Separators follow the actual wrapped rows: the first visible field on each row has no leading line. Hidden fields do not leave a separator. Native nested layouts remain in control of their own internal composition.

Use the package's `MetadataEntry` when an icon should sit beside the **whole label and value**, rather than just the value:

```php
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use MortalKiller\FilamentPageHeader\Components\MetadataEntry;

Header::make()->metadata([
    MetadataEntry::make('customer_number')
        ->label(__('Customer number'))
        ->fieldIcon(Heroicon::OutlinedHashtag)
        ->copyable(),
    MetadataEntry::make('email')
        ->fieldIcon(Heroicon::OutlinedEnvelope)
        ->fieldIconPosition(IconPosition::After)
        ->fieldIconSize(32),
]);
```

`fieldIconSize()` accepts a positive integer in CSS pixels or a closure returning one; the default is 24. Zero, negative and invalid dynamic values are rejected. The package leaves 16 pixels between the icon and its native field content, with enough room for the text when fields wrap.

`fieldIconPosition()` defaults to `IconPosition::Before` (left in left-to-right layouts); `After` places the icon on the right. Both methods accept closures with native Filament utility injection. Return `null` from `fieldIcon()` to omit the icon and its space. Icons are decorative; the visible label/value retain their accessible meaning.

`MetadataEntry` extends native `TextEntry`, preserving formatting, placeholders, copyable values, links, visibility and child actions. Native `icon()` / `iconPosition()` remain available for an icon inside the value; the package's `fieldIcon()` / `fieldIconPosition()` control the entire field. Plain `TextEntry` and other native components can still be used without field icons. The consuming application only declares this API; it needs no layout CSS or Blade overrides.

### Images and icons

```php
use Filament\Infolists\Components\ImageEntry;
use MortalKiller\FilamentPageHeader\Components\Heading;

$header
    ->avatar(ImageEntry::make('photo'))
    ->initials(fn (?Model $record) => $record?->getAttribute('name'));
```

Pass a URL or closure to `avatar()` for a browser-ready image URL. Use a native `ImageEntry` for stored paths, disk selection, private temporary URLs and image visibility; the package retains Filament's storage handling. `initials()` receives a full name, takes the first two words and supplies the native avatar fallback when image content is empty. The same name supplies alternative text for URL avatars; use native image attributes for custom image descriptions. URL avatars support HTTP(S) and relative URLs, and reject other schemes.

For advanced composition, `headingSchema()` and `leading()` still accept native components. The package does not upload images or depend on a particular icon library.

### Product header example

```php
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Components\MetadataEntry;

Header::make()
    ->heading(fn ($record) => $record->name)
    ->image(fn ($record) => $record->image_url)
    ->initials(fn ($record) => $record->name)
    ->descriptionSchema([
        TextEntry::make('supplier_code')->hiddenLabel()->copyable(),
    ])
    ->badges([
        TextEntry::make('status')->badge(),
    ])
    ->metadata([
        MetadataEntry::make('supplier.name')->label(__('Supplier'))
            ->fieldIcon(Heroicon::OutlinedTruck)->fieldIconSize(24),
        MetadataEntry::make('brand.name')->label(__('Brand'))
            ->fieldIcon(Heroicon::OutlinedTag),
    ])
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->show(HeaderPart::Image, HeaderPart::Description, HeaderPart::Badges));
```

The example assumes the consuming model provides these fields; image selection and business state belong to that application. `image()` shows the entire image in a square frame with soft corners: 96 px on desktop, 80 px below 768 px and 32 px when compact. Images and avatars are vertically centered with the full identity block (heading, description and badges). It accepts the same URL/closure/native ImageEntry inputs as `avatar()`. Calling `avatar()` again restores circular presentation. Both shrink in compact mode. The compact configuration above keeps the copyable supplier code visible while the metadata collapses. Create pages can continue to use their existing native header.

### Share configuration across a resource

Define a class such as `App\Filament\Resources\Orders\Schemas\OrderHeader` with `public static function configure(Schema $schema): Schema`. The trait discovers `{Model}Header` beside the resource by convention, including parent resource namespaces.

Alternatively, map the class explicitly on the panel plugin:

```php
PageHeaderPlugin::make()->schemaFor(OrderResource::class, OrderHeader::class);
```

An inline `headerSchema()` method overrides discovery. Add the trait only to pages that should use the shared schema. A page without a schema, or with an empty schema, uses the native header. A `getHeader()` method defined on the page itself keeps precedence. Without panel activation, the trait falls back to the native header.

### Native Filament surfaces

The header uses Filament's native `fi-section` surface, including its background, ring and shadow in light/dark mode. Text and avatar colors read the panel's `--gray-*` palette. Internal dividers match native sections: gray-200 in light mode and white at 10% opacity in dark mode. No consumer CSS or theme rebuild is required.

## Sticky and compact modes

```php
PageHeaderPlugin::make(); // Normal scrolling.
PageHeaderPlugin::make()->sticky(); // Pin the full header.
PageHeaderPlugin::make()->compact(); // Compact after reaching the sticky edge.
PageHeaderPlugin::make()->sticky()->compactBelow(1024);
```

`compactBelow(1024)` applies strictly below 1024 CSS pixels; at 1024 and above the configured base mode applies. It is a viewport threshold, not a device detector. At the top of the page the full layout remains visible. Compact mode retains the heading, badges, a smaller avatar and all native actions. It hides description, metadata, summary and additional schema content by default.

A Header inherits the current panel settings. Override its mode directly:

```php
Header::make()->heading(__('Customers'))->compact();
Header::make()->heading(__('Customers'))->normal();
Header::make()->heading(__('Customers'))->sticky()->compactBelow(768);
```

`normal()`, `sticky()` and `compact()` replace the inherited mode, responsive rules and compact threshold; the last selected mode wins. Call `compactBelow()` after selecting the mode. A header override never mutates another header or the panel defaults. The first top-level Header controls the page header's scroll behavior.

Advanced configurations can still use `mode(HeaderMode::...)`, `responsive([minimumWidth => HeaderMode::...])` on the plugin, or immutable `HeaderOptions` through `options()` / `pageHeaderOptions()`. The highest matching minimum width wins; `compactBelow()` takes precedence below its threshold.

### Offset and compact content

The default offset is derived from visible native topbars. Set an explicit pixel offset, or customize the selector for a different layout:

```php
PageHeaderPlugin::make()->offset(80);
PageHeaderPlugin::make()->topbarSelector('.my-topbar, .my-announcement-bar');
```

Passing `offset(null)` restores automatic detection; `topbarSelector(null)` disables topbar detection. Sticky positioning is bounded by the real scroll container and its ancestors. Avoid short wrappers or unintended `overflow: hidden` ancestors that prevent CSS sticky from reaching the page content.

Without `whenCompact()`, the existing defaults remain: image/avatar, heading, badges and native page actions stay visible; description, metadata, summary and extra content collapse.

Configure compact content with a typed callback:

```php
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

Header::make()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->show(HeaderPart::Image, HeaderPart::Description, HeaderPart::Badges)
        ->only(HeaderPart::Metadata, ['supplier.name', 'header_variants']));
```

`whenCompact()` configures content only: select `compact()` on the header or plugin to activate scroll compaction. Each callback starts with no optional blocks selected. An empty callback retains only the heading and native page actions, which cannot be hidden through this API. Each header has its own configuration; calling `whenCompact()` again replaces it.

| HeaderPart | Content |
| --- | --- |
| `Image` | Image, avatar, initials or custom leading content. |
| `Description` | Description text or native description schema. |
| `Badges` | Status badges. |
| `Metadata` | Information fields. |
| `Summary` | Summary metrics. |
| `Content` | Extra components declared through `schema()`. |

- `show(HeaderPart ...$parts)` adds complete blocks to the compact selection.
- `only(HeaderPart $part, array $fields)` enables that block with only the listed direct fields. It takes precedence over `show()` for that block regardless of call order. Repeating `only()` replaces that block's field list; `only(..., [])` hides it.
- Field identifiers are the names passed to native entries' `make()`, including relationship names such as `supplier.name`. Other schema components use their local `key()` / state path. For a nested layout, select its explicit key to retain the whole layout; this API does not search its descendants. Named schema actions can also be selected. Image/leading content supports `show()` only.
- Missing or currently unavailable fields do not become visible. If none of the selected fields are available, the block collapses without leaving an empty details strip. Empty/invalid field identifiers are rejected.
- Native `visible()` / `hidden()` conditions and permissions remain authoritative. Compaction only changes presentation of already-rendered content; it is not an authorization boundary. Components and actions are not duplicated, and scrolling sends no Livewire requests.
- Separators follow the visible rows in both expanded and compact layouts. Focused content stays accessible until focus leaves it. Images continue to shrink when compact.

`hideWhenCompact()` and `retainSummaryWhenCompact()` remain deprecated compatibility methods. New configuration should use `whenCompact()`; do not mix the APIs. If mixed, the last configuration method selects the active API. Existing per-component `data-fph-hide-compact` attributes remain supported for legacy views.

Breadcrumbs render above and outside the header card, and scroll with the page. They never occupy the pinned header. The former `HeaderOptions::hideBreadcrumbsWhenCompact()` option remains readable for compatibility with custom views, but has no effect on the package view.

The browser handles scrolling and compaction without Livewire requests or duplicated action instances. A stable expanded footprint prevents layout jumps. On very short viewports or unusually tall headers, pinning is temporarily disabled so the page remains usable. Reduced-motion preferences are respected.

External job/provider changes are not polled by this package. The application must refresh its data through its existing Livewire events or refresh mechanisms.

## Light and dark appearance

The package owns the card surface, spacing, dividers, avatars and responsive composition. When pinned, the card has square top corners and retains rounded bottom corners; scrolling back to the top restores all four rounded corners. Colors follow Filament's gray tokens and its `.dark` theme class. Native badges and actions keep their configured semantic colors. No consumer CSS, Blade overrides or Tailwind build is needed.

## Migration from the former API

- Replace `HeaderLayout` with `Header`.
- Replace `subheading()` with `description()` and `trailing()` with `summary()`.
- Pass metadata and metrics directly; remove Grids used only to arrange these standard regions.
- Replace manual photo/initials composition with `avatar()` and `initials()` where appropriate.
- Prefer `sticky()->compactBelow(1024)` over the equivalent panel breakpoint map.
- Update compact slot names and explicitly opt in if a summary must stay visible.
- Publish assets again; PHP/Blade symlinks do not update published CSS/JS.

This is intentionally a breaking change. Migrate consuming schemas before using this checkout and select a major release separately before publication.

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
