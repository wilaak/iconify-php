<?php

use PHPUnit\Framework\TestCase;
use Wilaak\Iconify\_IconCtx as IconContext;
use Wilaak\Iconify as Icon;

abstract class RenderTestCase extends TestCase
{
    protected string $localPath;
    protected string $remotePath;

    protected function setUp(): void
    {
        $this->localPath  = sys_get_temp_dir() . '/iconify-php-local-'  . uniqid();
        $this->remotePath = sys_get_temp_dir() . '/iconify-php-remote-' . uniqid();
        mkdir($this->localPath, 0755, true);
        mkdir($this->localPath . '/badges', 0755, true);
        mkdir($this->remotePath, 0755, true);

        file_put_contents(
            $this->localPath . '/home.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>'
        );
        file_put_contents(
            $this->localPath . '/badges/admin.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><circle cx="8" cy="8" r="8"/></svg>'
        );

        Icon\init_remote(storePath: $this->remotePath, servePath: '/icons');
        Icon\init_local(storePath: $this->localPath, servePath: '/assets/icons');
    }

    protected function tearDown(): void
    {
        IconContext::reset();

        array_map('unlink', glob($this->localPath . '/badges/*') ?: []);
        rmdir($this->localPath . '/badges');
        array_map('unlink', glob($this->localPath . '/*') ?: []);
        rmdir($this->localPath);

        foreach (glob($this->remotePath . '/*.svg') ?: [] as $f) unlink($f);
        rmdir($this->remotePath);
    }

    protected function capture(callable $fn): string
    {
        ob_start();
        $fn();
        return ob_get_clean();
    }

    protected function placeRemoteIcon(string $icon, string $svg): void
    {
        file_put_contents($this->remotePath . '/' . $icon . '.svg', $svg);
    }
}
