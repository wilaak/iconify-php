<?php

namespace Wilaak\Iconify;

/**
 * Set the filesystem path where Iconify SVGs are stored and the public URL prefix used to serve them.
 *
 * @param string $storePath Absolute filesystem path to the cache directory.
 * @param string $servePath Public URL prefix used in mask-image URLs.
 */
function init_remote(
    string $storePath,
    string $servePath,
): void {
    $ctx = _IconCtx::get();
    $ctx->remotePath       = \rtrim($storePath, '/');
    $ctx->remotePathPublic = \rtrim($servePath, '/');
    $ctx->downloaded       = [];
    $ctx->cacheMask        = [];
    $ctx->cacheInline      = [];
}

/**
 * Set the filesystem path for local SVG files and the public URL prefix used to serve them.
 *
 * @param string $storePath Absolute filesystem path to the custom icons directory.
 * @param string $servePath Public URL prefix used in mask-image URLs.
 */
function init_local(
    string $storePath,
    string $servePath,
): void {
    $ctx = _IconCtx::get();
    $ctx->localPath       = \rtrim($storePath, '/');
    $ctx->localPathPublic = \rtrim($servePath, '/');
}

/**
 * Register a callback invoked when an icon cannot be found or fetched.
 *
 * @param \Closure(string $icon, string $reason): void $handler
 */
function on_error(\Closure $handler): void
{
    _IconCtx::get()->onError = $handler;
}

/**
 * Render an icon as a CSS mask-image SVG element.
 * Downloads the icon on first use.
 *
 * @param string $icon   Iconify name (e.g. `mdi:home`) or custom filename (e.g. `logo.svg`).
 * @param string $width  CSS width value.
 * @param string $height CSS height value.
 */
function mask(string $icon, string $width = '24px', string $height = '24px'): void
{
    _render(_RenderMode::MASK, $icon, $width, $height);
}

/**
 * Render an icon as an inline SVG element with the original paths embedded.
 * Downloads the icon on first use.
 *
 * @param string $icon   Iconify name (e.g. `mdi:home`) or custom filename (e.g. `logo.svg`).
 * @param string $width  CSS width value.
 * @param string $height CSS height value.
 */
function inline(string $icon, string $width = '24px', string $height = '24px'): void
{
    _render(_RenderMode::INLINE, $icon, $width, $height);
}

function _render(int $mode, string $icon, string $width, string $height): void
{
    $ctx = _IconCtx::get();

    if (!_is_local_icon($icon)) {
        if ($ctx->remotePath === null) {
            return;
        }
        if (!($ctx->downloaded[$icon] ?? false)) {
            $ctx->downloaded[$icon] = true;
            if (!_ensure_downloaded($ctx, $icon)) {
                return;
            }
        }
    }

    match ($mode) {
        _RenderMode::MASK   => _render_mask($ctx, $icon, $width, $height),
        _RenderMode::INLINE => _render_inline($ctx, $icon, $width, $height),
    };
}

class _RenderMode
{
    const MASK   = 0;
    const INLINE = 1;
}

class _IconCtx
{
    function __construct(
        public ?string   $remotePath       = null,
        public ?string   $remotePathPublic = null,
        public ?string   $localPath        = null,
        public ?string   $localPathPublic  = null,
        public string    $apiUrl           = 'https://api.iconify.design',
        public array     $downloaded       = [],
        public array     $cacheMask        = [],
        public array     $cacheInline      = [],
        public ?\Closure $onError          = null,
    ) {}

    private static ?self $instance = null;

    static function get(): self
    {
        return self::$instance ??= new self();
    }

    static function reset(): void
    {
        self::$instance = null;
    }
}

function _render_mask(_IconCtx $ctx, string $icon, string $width, string $height): void
{
    $cached = $ctx->cacheMask[$icon] ?? null;

    if ($cached === false) {
        return;
    }

    if ($cached === null) {
        $filePath = _resolve_path($ctx, $icon);
        if (_is_local_icon($icon)) {
            $publicPath = $ctx->localPathPublic;
            $publicFile = $icon;
        } else {
            $publicPath = $ctx->remotePathPublic;
            $publicFile = "{$icon}.svg";
        }
        if (!\file_exists($filePath)) {
            $ctx->cacheMask[$icon] = false;
            if ($ctx->onError !== null) ($ctx->onError)($icon, 'Icon file not found');
            return;
        }
        $ctx->cacheMask[$icon] = $cached = "url('{$publicPath}/{$publicFile}')";
    }

    \printf(
        '<svg width="%s" height="%s" class="iconify-mask" style="mask-image:%s;"></svg>',
        $width,
        $height,
        $cached
    );
}

function _render_inline(_IconCtx $ctx, string $icon, string $width, string $height): void
{
    $cached = $ctx->cacheInline[$icon] ?? null;

    if ($cached === false) {
        return;
    }

    if ($cached === null) {
        $filePath = _resolve_path($ctx, $icon);

        if (!\file_exists($filePath)) {
            $ctx->cacheInline[$icon] = false;
            if ($ctx->onError !== null) {
                ($ctx->onError)($icon, 'Icon file not found');
            }
            return;
        }

        $content = \file_get_contents($filePath);
        \preg_match('/viewBox=["\']([^"\']+)["\']/', $content, $matches);

        $viewBox = $matches[1] ?? '0 0 24 24';
        $inner   = \preg_replace('/^\s*<svg[^>]*>/', '', $content, 1);
        $inner   = \trim(\preg_replace('/<\/svg>\s*$/', '', $inner));

        $ctx->cacheInline[$icon] = [$viewBox, $inner];
    } else {
        [$viewBox, $inner] = $cached;
    }

    echo "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"{$viewBox}\" width=\"{$width}\" height=\"{$height}\">{$inner}</svg>";
}

function _is_local_icon(string $icon): bool
{
    return \str_ends_with($icon, '.svg');
}

function _resolve_path(_IconCtx $ctx, string $icon): string
{
    if (_is_local_icon($icon)) {
        if ($ctx->localPath === null) {
            throw new \RuntimeException("Custom icon '{$icon}' requested but Icon\\init() was called without a custom path.");
        }
        return $ctx->localPath . '/' . $icon;
    }

    return $ctx->remotePath . '/' . $icon . '.svg';
}

function _fetch_icon_to_disk(string $storePath, string $icon): ?string
{
    return _ensure_downloaded_fetch(_IconCtx::get(), $storePath, $icon);
}

function _ensure_downloaded_fetch(_IconCtx $ctx, string $remote_path, string $icon): ?string
{
    [$collection, $icon_name] = \explode(':', $icon, 2);

    $url = $ctx->apiUrl . '/' . $collection . '.json?icons=' . $icon_name;

    $ch = \curl_init($url);
    \curl_setopt_array($ch, [
        \CURLOPT_RETURNTRANSFER => true,
        \CURLOPT_TIMEOUT        => 10,
        \CURLOPT_FAILONERROR    => true,
    ]);
    $response = \curl_exec($ch);
    $error    = $response === false ? \curl_error($ch) : null;

    if ($error !== null) {
        return 'API request failed: ' . $error;
    }

    $data      = \json_decode($response, true);
    $icon_data = $data['icons'][$icon_name] ?? null;
    $body      = $icon_data['body'] ?? null;
    if ($body === null) {
        return 'Icon not found in API response';
    }

    $width   = $icon_data['width']  ?? $data['width']  ?? 24;
    $height  = $icon_data['height'] ?? $data['height'] ?? 24;
    $viewbox = "0 0 $width $height";

    $file_path  = $remote_path . '/' . $icon . '.svg';
    $directory  = \dirname($file_path);
    if (!\is_dir($directory)) {
        \mkdir($directory, 0755, true);
    }

    \file_put_contents($file_path, \sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="%s">%s</svg>',
        $viewbox,
        $body
    ));

    return null;
}

function _ensure_downloaded(_IconCtx $ctx, string $icon): bool
{
    $filePath = $ctx->remotePath . '/' . $icon . '.svg';

    if (\file_exists($filePath)) {
        return true;
    }

    $error = _ensure_downloaded_fetch($ctx, $ctx->remotePath, $icon);
    if ($error !== null) {
        if ($ctx->onError !== null) ($ctx->onError)($icon, $error);
        return false;
    }

    return true;
}
