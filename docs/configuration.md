# Header configuration

[Back to the README](../README.md) · [Installation and first header](../README.md#installation)

Configure content with native Filament schema components. Examples below assume the imports shown in each example and an existing page using `HasPageHeader`.

## Layout slots

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
| `icon($icon)` | Native icon fallback when no image or initials can be rendered. |
| `initialsColor($color)` | Panel color alias, native Filament palette or closure for the initials fallback background. |
| `initialsTextColor($color)` | Optional CSS color or closure for the initials fallback text. |
| `leading([...])` | Optional icon, avatar or logo. Use native entries; the package does not manage uploads. |
| `metadata([...])` | References, dates, links and other secondary information. |
| `summary([...])` | Optional summary or total, separate from the page's native actions. |
| `schema([...])` | Additional components or native actions below the title. |
| `whenCompact(fn (CompactHeader $compact) => ...)` | Select compact blocks and fields using HeaderPart enums. |

Slot methods accept arrays or closures. Badge entry labels are hidden automatically. Metadata and summary arrange native components without requiring a Grid; use explicit native layouts only for custom compositions. Use normal Filament visibility, colors, icons and authorization APIs. Do not put persistence, API requests or expensive calculations inside rendering closures.

A page should render one principal `h1`. `Header` supplies one by default; preserve that rule when supplying `headingSchema()`. Optional empty slots are omitted. Badges wrap instead of forcing page-wide horizontal scrolling.

Native `getHeaderActions()` remains independent. Its actions render once beside the identity, right-aligned, and wrap to right-aligned rows when space runs out. On mobile, metadata and summary precede a single column of full-width actions. Native button groups keep their segments on one row, and standalone icon-only menu triggers are centered below the main actions. The first top-level Header hosts the native actions; arbitrary schemas without a Header keep an independent native action group. Existing `formId`, ActionGroup, modal, confirmation and authorization behavior is retained. An action in the schema is additional content, not a replacement for native header actions.

## Metadata separators and field icons

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

## Images and icons

```php
use Filament\Infolists\Components\ImageEntry;
use MortalKiller\FilamentPageHeader\Components\Heading;

$header
    ->avatar(ImageEntry::make('photo'))
    ->initials(fn (?Model $record) => $record?->getAttribute('name'));
```

Pass a URL or closure to `avatar()` for a browser-ready image URL. Use a native `ImageEntry` for stored paths, disk selection, private temporary URLs and image visibility; the package retains Filament's storage handling. `initials()` receives a full name, takes the first two words and supplies the native avatar fallback when image content is empty. The same name supplies alternative text for URL avatars; use native image attributes for custom image descriptions. URL avatars support HTTP(S) and relative URLs, and reject other schemes.

Use `initialsColor()` to give the fallback a semantic panel color or a native palette. A color alias such as `primary`, `success` or a custom color registered on the panel follows that panel's palette. You can also pass `Color::Blue` or a closure returning either value. The package uses shade 600 for the background and chooses the lighter or darker palette extreme with the highest contrast for the initials. This keeps the fallback legible in light and dark panels. `initialsTextColor()` accepts a CSS color string, or a closure returning one, when a deliberate text color is required.

```php
use Filament\Support\Colors\Color;

Header::make()
    ->initials(fn ($record) => $record->name)
    ->initialsColor('primary');

Header::make()
    ->initials(fn ($record) => $record->name)
    ->initialsColor(fn ($record) => $record->is_vip ? Color::Amber : Color::Blue)
    ->initialsTextColor('white');
```

These methods affect only the initials fallback. A rendered `avatar()`, `image()` or `ImageEntry` keeps its own visual content.

`icon()` completes the automatic identity fallback chain. The package renders one visual only: a custom `leading()` slot when present, then an available avatar/image, then generated initials, and finally the icon. Pass a `Heroicon`, another backed icon enum, a string icon name or a closure returning one:

```php
use Filament\Support\Icons\Heroicon;

Header::make()
    ->avatar(fn ($record) => $record->photo_url)
    ->initials(fn ($record) => $record->name)
    ->icon(Heroicon::OutlinedUser);
```

This shows the image when it resolves, initials when the image is unavailable, and the icon when neither is available. The icon uses the same responsive circular identity frame as initials.

For advanced composition, `headingSchema()` and `leading()` still accept native components. The package does not upload images or depend on a particular icon library.

## Product header example

For the simple product shown in the README, see variant 12 in the [standalone gallery](../workbench/app/Pages/HeaderGallery.php). It uses `image()`, two native actions and three metadata entries; the demo panel selects an indigo primary color.

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

## Share configuration across a resource

Define a class such as `App\Filament\Resources\Orders\Schemas\OrderHeader` with `public static function configure(Schema $schema): Schema`. The trait discovers `{Model}Header` beside the resource by convention, including parent resource namespaces.

Alternatively, map the class explicitly on the panel plugin:

```php
PageHeaderPlugin::make()->schemaFor(OrderResource::class, OrderHeader::class);
```

An inline `headerSchema()` method overrides discovery. Add the trait only to pages that should use the shared schema. A page without a schema, or with an empty schema, uses the native header. A `getHeader()` method defined on the page itself keeps precedence. Without panel activation, the trait falls back to the native header.

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

`whenCompact()` configures content only: select `compact()` on the header or plugin to activate scroll compaction. Each callback starts with no optional blocks selected. An empty callback retains only the heading and native page actions, which cannot be hidden through this API. Each header has its own configuration; calling `whenCompact()` again replaces it. When selected, description content remains directly below the compact heading in a smaller supporting line; it never shares the heading's row.

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
- Separators follow the visible rows in both expanded and compact layouts. Focused content stays accessible until focus leaves it. Images and initials remain centered with the compact identity block and continue to shrink responsively. A compact header gains a subtle lower shadow only after it reaches the sticky edge; that shadow and the reduced spacing animate over 150ms.

Breadcrumbs render above and outside the header card and scroll with the page. For deprecated compact methods, see [Migration from v1](migration.md).

The browser handles scrolling and compaction without Livewire requests or duplicated action instances. Its sticky state remains browser-owned across unrelated Livewire updates, so changing a relation-manager tab does not reset an already compact header. A stable expanded footprint prevents layout jumps. On very short viewports or unusually tall headers, pinning is temporarily disabled so the page remains usable. Reduced-motion preferences are respected.

Internal height measurements restore the displayed layout before re-enabling transitions. Loading the page, resizing its content or refreshing Livewire does not animate a temporary measurement state. While the header itself is transitioning, new measurements wait until its transitions finish or are cancelled; scroll state continues updating. This lets the compact/expanded animation complete without being interrupted by its own height changes. Reduced-motion users do not incur an animation delay.

External job/provider changes are not polled by this package. The application must refresh its data through its existing Livewire events or refresh mechanisms.

## Light and dark appearance

The package owns the card surface, spacing, dividers, avatars and responsive composition. When pinned, the card has square top corners and retains rounded bottom corners; scrolling back to the top restores all four rounded corners. Colors follow Filament's gray tokens and its `.dark` theme class. Native badges and actions keep their configured semantic colors. No consumer CSS, Blade overrides or Tailwind build is needed.
