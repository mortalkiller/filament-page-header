# Header configuration

[Back to the README](../README.md) · [Generator](generator.md) · [Installation and first header](../README.md#installation)

Configure content with native Filament schema components. Examples below assume the imports shown in each example and an existing page using `HasPageHeader`.

## Stylesheet loading

The plugin includes its stylesheet in the initial document head, after the panel theme, using Filament's `STYLES_AFTER` hook. It loads on every page of a panel that enables the plugin, including native pages, so SPA navigation into a custom header already has the required styles. Panels without the plugin do not include this stylesheet. The Alpine component remains loaded on demand.

Keep publishing assets with `php artisan filament:assets` after package updates. No custom theme import is required. Theme overrides still follow normal CSS cascade rules; for equally specific rules in the same cascade layer, the package stylesheet comes after the panel theme.

## Breadcrumbs and sub-navigation

```php
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Enums\BreadcrumbPosition;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

Header::make()
    ->breadcrumbs(BreadcrumbPosition::Inside)
    ->subNavigation()
    ->compact()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->show(HeaderPart::Breadcrumbs, HeaderPart::SubNavigation));
```

`BreadcrumbPosition::Outside` is the default and preserves the original breadcrumb location above the card. `Inside` places them above the identity content in the card. `Hidden` omits them entirely. The native page provides labels and URLs; disabling breadcrumbs on the panel still hides them. The position accepts a closure with the usual Filament schema utilities.

Inside breadcrumbs and sub-navigation are hidden in compact mode by default. Select their `HeaderPart` values with `show()` to keep them. These are whole native navigation blocks: `only()` rejects field selection for either part. Selecting `Breadcrumbs` never overrides `Hidden` or panel settings, and selecting `SubNavigation` never enables a disabled `subNavigation()`.

Outside breadcrumbs normally scroll away with the page. Explicitly retaining them puts them in the same sticky surface as the card while keeping them visually outside it. The full surface is measured and its expanded height reserved, so compacting does not move the page content. In normal mode the entire surface scrolls normally. As with other header content, pinning is disabled when the surface would take too much of a short viewport.

`subNavigation()` accepts a boolean or closure and is disabled by default. It uses the native Page/Resource navigation, including record pages returned by `getRecordSubNavigation()` and `ManageRelatedRecords`. Desktop renders Filament's sub-navigation tabs; mobile renders its sub-navigation dropdown. The package reuses cached native navigation groups/items and their filtering, URLs, labels, icons, badges, active state and SPA links.

On desktop, the tabs are part of the header itself: they follow the header content with the standard `1rem` gap and use an active underline in the panel's primary color. They do not receive a second rounded container, background or dividing border. The active underline sits directly on the header's lower edge, with `1rem` of bottom padding below the labels.

This edge alignment applies only while desktop navigation is visible, including when keyboard focus keeps it open in compact mode. When navigation hides, the header keeps its original compact bottom padding (`0.75rem`). Headers without sub-navigation retain their normal spacing. Mobile keeps the original dropdown layout, including the separator and `1rem` of padding above it.

The native location is suppressed only while rendering a page whose actual package header has opted in. Empty schemas, custom header overrides, panels without the plugin and `subNavigation(false)` keep native rendering, including Start/End/Top positions. The header invokes the native Top and mobile-menu render hooks around the relocated components. Start/End sidebar hooks apply only when Filament renders those sidebars. Empty navigation produces no strip or border. Navigation configuration belongs to the first `Header` in the schema, like native header actions.

Relation Manager tabs use a separate Filament system. They remain in the page content and keep their Livewire state. Combining them with the main content tab, or changing `ContentTabPosition::Before` / `After`, does not affect header navigation. No Relation Manager settings are required to use `subNavigation()`.

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
| `initialsBgColor($color)` | Panel color alias, native Filament palette or closure for the initials fallback background. |
| `initialsTextColor($color)` | Optional panel color alias, native palette, CSS color or closure for the initials fallback text. |
| `iconBgColor($color)` | Panel color alias, native Filament palette or closure for the icon fallback background. |
| `iconColor($color)` | Optional panel color alias, native palette, CSS color or closure for the icon fallback. |
| `leading([...])` | Optional icon, avatar or logo. Use native entries; the package does not manage uploads. |
| `metadata([...])` | References, dates, links and other secondary information. |
| `summary([...])` | Optional summary or total, separate from the page's native actions. |
| `schema([...])` | Additional components or native actions below the title. |
| `breadcrumbs(BreadcrumbPosition::Inside)` | Position the page’s native breadcrumbs; also accepts a closure. |
| `subNavigation($condition = true)` | Move native Page/Resource sub-navigation into the header; accepts a boolean or closure. |
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

All four color methods accept a panel color alias, a native palette such as `Color::Blue`, or a closure returning either value. Background colors use shade 600 and choose the lighter or darker palette extreme with the highest contrast for initials or icons. This keeps each fallback legible in light and dark panels. When you explicitly set `initialsTextColor()` or `iconColor()` with a palette or alias, the package uses its shade 600. CSS color strings, such as `white`, remain available for a deliberate foreground color.

```php
use Filament\Support\Colors\Color;

Header::make()
    ->initials(fn ($record) => $record->name)
    ->initialsBgColor('primary');

Header::make()
    ->initials(fn ($record) => $record->name)
    ->initialsBgColor(fn ($record) => $record->is_vip ? Color::Amber : Color::Blue)
    ->initialsTextColor(Color::Blue);
```

These initials methods affect only the initials fallback. A rendered `avatar()`, `image()` or `ImageEntry` keeps its own visual content.

`icon()` completes the automatic identity fallback chain. The package renders one visual only: a custom `leading()` slot when present, then an available avatar/image, then generated initials, and finally the icon. Pass a `Heroicon`, another backed icon enum, a string icon name or a closure returning one:

```php
use Filament\Support\Icons\Heroicon;

Header::make()
    ->avatar(fn ($record) => $record->photo_url)
    ->initials(fn ($record) => $record->name)
    ->icon(Heroicon::OutlinedUser)
    ->iconBgColor('primary')
    ->iconColor(Color::Blue);
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

## Record and context resolution

The header schema uses the current page record by default. On Resource pages with a record, `getPageHeaderRecord()` returns that record; pages without a record receive `null`. The trait applies this value to the native Filament `Schema` before your `headerSchema()` or shared `*Header::configure()` method runs.

Override `getPageHeaderRecord()` when the header should describe something other than the page's primary record:

```php
use Illuminate\Database\Eloquent\Model;

public function getPageHeaderRecord(): Model|array|null
{
    // Return the model or array that should back the header schema.
}
```

Native Filament schema utilities keep working with this context. A closure parameter named `$record` receives the configured model, array or `null`; model type injection also works when the context is an Eloquent model. Entries resolve their state from the same schema record.

Changing the header record does **not** replace the page's own Resource record, change form persistence, or grant access to anything. Policies, page authorization, action authorization and data loading remain the consuming application's responsibility.

### Tenant-backed pages

A tenant can be used as the header record without coupling this package to a tenancy implementation. For a Filament tenant-aware panel:

```php
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

public function getPageHeaderRecord(): ?Model
{
    return Filament::getTenant();
}
```

The existing header schema can then use normal record injection:

```php
Header::make()
    ->heading(fn (?Model $record): string => $record?->getAttribute('name') ?? __('Workspace'))
    ->initials(fn (?Model $record): ?string => $record?->getAttribute('name'));
```

If the page can also render outside a tenant context, keep the closure nullable or provide an application-specific fallback.

### Parent records

Nested Resource pages can make their parent record the header context:

```php
use Illuminate\Database\Eloquent\Model;

public function getPageHeaderRecord(): ?Model
{
    return $this->getParentRecord();
}
```

This is useful when a nested list or create page is conceptually about the parent entity even though the page does not have its own persisted record yet.

For example, an order-items page can keep the header focused on the parent order while the table or form continues to manage item records independently.

### Settings and singleton models

A settings page can resolve an application-owned singleton model and expose it to the header:

```php
use App\Models\CompanySettings;
use Illuminate\Database\Eloquent\Model;

public function getPageHeaderRecord(): ?Model
{
    return CompanySettings::query()->first();
}
```

The header may then read that model using normal Filament schema components:

```php
Header::make()
    ->heading(fn (?CompanySettings $record): string => $record?->company_name ?? __('Company settings'))
    ->description(fn (?CompanySettings $record): ?string => $record?->billing_email);
```

The package does not require a particular settings library. Resolve the model or context using the same application service or repository you would use elsewhere.

### Custom pages backed by a model

Custom Filament pages can return any application model they already resolved:

```php
use App\Models\Customer;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

class CustomerOverview extends \Filament\Pages\Page
{
    use HasPageHeader;

    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getPageHeaderRecord(): Model
    {
        return $this->customer;
    }

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Header::make()
                ->heading(fn (Customer $record): string => $record->name)
                ->description(fn (Customer $record): string => $record->email),
        ]);
    }
}
```

The routing and model resolution shown here belong to the application; the package only uses the returned record as the schema context.

### Array-backed custom pages

A header does not require an Eloquent model. Return an associative array when the page context is derived or does not belong to one model:

```php
/** @return array<string, mixed> */
public function getPageHeaderRecord(): array
{
    return [
        'name' => __('System status'),
        'environment' => app()->environment(),
        'region' => config('app.region'),
    ];
}
```

Use a parameter named `$record` for array context closures:

```php
use Filament\Infolists\Components\TextEntry;

Header::make()
    ->heading(fn (array $record): string => $record['name'])
    ->metadata([
        TextEntry::make('environment')->label(__('Environment')),
        TextEntry::make('region')->label(__('Region')),
    ]);
```

Native entries resolve keys such as `environment` and `region` from the array record. Do not type the closure as an Eloquent model when the page returns an array.

### Create pages and null records

Create pages normally have no persisted record yet, so the default header record is `null`. Header closures that depend on the future record should therefore accept `null`:

```php
use Illuminate\Database\Eloquent\Model;

Header::make()
    ->heading(fn (?Model $record): string => $record?->getAttribute('name') ?? __('Create customer'));
```

If the create page should instead describe a tenant, parent record or another stable context, override `getPageHeaderRecord()` with that value. This changes only the header schema context; it does not change the record being created.

### Multiple panels

Register the plugin separately on every panel that should use custom page headers:

```php
// AdminPanelProvider
return $panel
    ->id('admin')
    ->plugin(PageHeaderPlugin::make());

// OperationsPanelProvider
return $panel
    ->id('operations')
    ->plugin(PageHeaderPlugin::make());
```

Panels that do not register the plugin keep their native Filament headers. Plugin options and explicit `schemaFor()` mappings are configured per plugin instance, so each panel can choose its own defaults and mappings.

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

Without `whenCompact()`, the existing defaults remain: image/avatar, heading, badges and native page actions stay visible; description, metadata, summary, extra content, inside breadcrumbs and sub-navigation collapse.

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
| `Breadcrumbs` | Native page breadcrumbs in their configured position. |
| `SubNavigation` | Native page/record navigation, when `subNavigation()` is enabled. |

- `show(HeaderPart ...$parts)` adds complete blocks to the compact selection.
- `only(HeaderPart $part, array $fields)` enables that block with only the listed direct fields. It takes precedence over `show()` for that block regardless of call order. Repeating `only()` replaces that block's field list; `only(..., [])` hides it.
- Field identifiers are the names passed to native entries' `make()`, including relationship names such as `supplier.name`. Other schema components use their local `key()` / state path. For a nested layout, select its explicit key to retain the whole layout; this API does not search its descendants. Named schema actions can also be selected. Image/leading content and native navigation support `show()` only.
- Missing or currently unavailable fields do not become visible. If none of the selected fields are available, the block collapses without leaving an empty details strip. Empty/invalid field identifiers are rejected.
- Native `visible()` / `hidden()` conditions and permissions remain authoritative. Compaction only changes presentation of already-rendered content; it is not an authorization boundary. Components and actions are not duplicated, and scrolling sends no Livewire requests.
- Separators follow the visible rows in both expanded and compact layouts. Focused content stays accessible until focus leaves it. Images and initials remain centered with the compact identity block and continue to shrink responsively. A compact header gains a subtle lower shadow only after it reaches the sticky edge; that shadow and the reduced spacing animate over 150ms.

Breadcrumbs default to the outside location; see [breadcrumbs and sub-navigation](#breadcrumbs-and-sub-navigation) for placement and compact visibility. For deprecated compact methods, see [Migration from v1](migration.md).

The browser handles scrolling and compaction without Livewire requests or duplicated action instances. Its sticky state remains browser-owned across unrelated Livewire updates, so changing a relation-manager tab does not reset an already compact header. A stable expanded footprint prevents layout jumps. On very short viewports or unusually tall headers, pinning is temporarily disabled so the page remains usable. Reduced-motion preferences are respected.

Internal height measurements restore the displayed layout before re-enabling transitions. Loading the page, resizing its content or refreshing Livewire does not animate a temporary measurement state. While the header itself is transitioning, new measurements wait until its transitions finish or are cancelled; scroll state continues updating. This lets the compact/expanded animation complete without being interrupted by its own height changes. Reduced-motion users do not incur an animation delay.

External job/provider changes are not polled by this package. The application must refresh its data through its existing Livewire events or refresh mechanisms.

## Light and dark appearance

The package owns the card surface, spacing, dividers, avatars and responsive composition. When pinned, the card has square top corners and retains rounded bottom corners; scrolling back to the top restores all four rounded corners. Colors follow Filament's gray tokens and its `.dark` theme class. Native badges and actions keep their configured semantic colors. No consumer CSS, Blade overrides or Tailwind build is needed.
