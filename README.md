# Iconify API

> [!NOTE]   
> This is a work in progress.

Drop Iconify icons into any PHP template.

Pairs well with the VSCode extension [Iconify IntelliSense](https://marketplace.visualstudio.com/items?itemName=antfu.iconify). See the [example](example/) for a working local dev setup.

## Install

```
composer require wilaak/iconify-php
```

## Usage

```php
use Wilaak\Iconify as Icon;

//
// Remote icons fetched from the Iconify API are cached here:
//
Icon\init_remote(
    storePath: '/var/www/storage/icons',
    servePath: '/icons',
);

//
// Local custom SVG files live here:
//
Icon\init_local(
    storePath: '/var/www/assets/icons',
    servePath: '/assets/icons',
);

//
// Handle errors (missing files, failed API requests):
//
Icon\on_error(function (string $icon, string $reason): void {
    error_log("Icon error [$icon]: $reason");
});

// CSS mask-image icon. The SVG file is fetched once and cached
// by the browser, regardless of how many times it appears on the page.
// Color is controlled via CSS currentColor (single-color only).
//
// This is the most efficient one for simple monochrome icons. For
// multicolor or animated icons, use inline() instead.
//
// Requires this CSS rule:
//
// .iconify-mask {
//     background: currentColor;
//     mask-position: center center;
//     mask-size: 100% auto;
//     mask-repeat: no-repeat;
// }
Icon\mask('mdi:home');
// <svg width="24px"
//      height="24px"
//      class="iconify-mask"
//      style="mask-image:url('/icons/mdi:home.svg');">
// </svg>

Icon\mask('mdi:home', '20px', '20px');

// Full SVG markup embedded.
// Use for multicolor icons or animations.
Icon\inline('mdi:home');
// <svg xmlns="http://www.w3.org/2000/svg"
//      viewBox="0 0 24 24"
//      width="24px"
//      height="24px">
//     <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
// </svg>

Icon\inline('mdi:home', '20px', '20px');
```
## Cli Tool

After `composer install`, a CLI tool is available at `vendor/bin/iconify-php`.

```
Usage: iconify-php <command> [options]

Commands:
  list   <cache-path>              List all downloaded icons
  clear  <cache-path>              Delete all downloaded SVG files
  fetch  <cache-path> <icon>       Pre-download a single icon (e.g. mdi:home)
  scan   <cache-path> <src-path>   Scan source for icon calls and download missing icons
```

```bash
# Scan your codebase and download any missing icons — run this in CI or during deploy
vendor/bin/iconify-php scan /var/www/storage/icons src/

# Download a single icon manually
vendor/bin/iconify-php fetch /var/www/storage/icons mdi:home

# See what has been downloaded
vendor/bin/iconify-php list /var/www/storage/icons

# Bust the icon cache
vendor/bin/iconify-php clear /var/www/storage/icons
```

## Todo

 - Cross-Site Scripting (XSS) detection for Inline SVG elements.
 - Improve scanner