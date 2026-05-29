<?php

namespace Wilaak\Iconify\Scanner;

function _scan_file_icons(string $content): array
{
    $flat = strtolower(preg_replace('/\s+/', ' ', $content));

    $nsAliases = _collect_ns_aliases($flat);
    $fnAliases = _collect_fn_aliases($flat);

    $icons = [];

    _collect_icons('/(?:\\\\)?Wilaak\\\\Iconify\\\\(?:mask|inline)\s*\(\s*[\'"]([^\'"]+)[\'"]/ i', $content, $icons);

    foreach ($nsAliases as $alias) {
        _collect_icons('/' . preg_quote($alias, '/') . '\\\\(?:mask|inline)\s*\(\s*[\'"]([^\'"]+)[\'"]/ i', $content, $icons);
    }

    foreach ($fnAliases as $localName => $_fn) {
        _collect_icons('/' . preg_quote($localName, '/') . '\s*\(\s*[\'"]([^\'"]+)[\'"]/ i', $content, $icons);
    }

    return array_keys($icons);
}

function _collect_ns_aliases(string $flat): array
{
    $aliases = [];

    if (preg_match_all('/use wilaak\\\\iconify(?: as (\w+))?\s*;/', $flat, $m)) {
        foreach ($m[1] as $alias) {
            $aliases[] = $alias !== '' ? $alias : 'iconify';
        }
    }

    if (preg_match_all('/use wilaak\\\\{([^}]+)}/', $flat, $m)) {
        foreach ($m[1] as $group) {
            foreach (preg_split('/\s*,\s*/', trim($group)) as $part) {
                if (preg_match('/^iconify(?: as (\w+))?$/', trim($part), $pm)) {
                    $aliases[] = isset($pm[1]) && $pm[1] !== '' ? $pm[1] : 'iconify';
                }
            }
        }
    }

    return $aliases;
}

function _collect_fn_aliases(string $flat): array
{
    $aliases = [];

    if (preg_match_all('/use function wilaak\\\\iconify\\\\(mask|inline)(?: as (\w+))?\s*;/', $flat, $m)) {
        foreach (array_map(null, $m[1], $m[2]) as [$fn, $alias]) {
            $aliases[$alias !== '' ? $alias : $fn] = $fn;
        }
    }

    if (preg_match_all('/use function wilaak\\\\iconify\\\\{([^}]+)}/', $flat, $m)) {
        foreach ($m[1] as $group) {
            foreach (preg_split('/\s*,\s*/', trim($group)) as $part) {
                if (preg_match('/^(mask|inline)(?: as (\w+))?$/', trim($part), $pm)) {
                    $aliases[isset($pm[2]) && $pm[2] !== '' ? $pm[2] : $pm[1]] = $pm[1];
                }
            }
        }
    }

    return $aliases;
}

function _collect_icons(string $pattern, string $content, array &$icons): void
{
    preg_match_all($pattern, $content, $matches);
    foreach ($matches[1] as $icon) {
        if (strpos($icon, ':') !== false) {
            $icons[$icon] = true;
        }
    }
}
