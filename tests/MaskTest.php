<?php

use Wilaak\Iconify\_IconCtx as IconContext;
use Wilaak\Iconify as Icon;

class MaskTest extends RenderTestCase
{
    public function testEmitsSvgWithMaskImage(): void
    {
        $output = $this->capture(fn() => Icon\mask('home.svg'));
        $this->assertStringContainsString('mask-image:', $output);
        $this->assertStringContainsString("url('/assets/icons/home.svg')", $output);
    }

    public function testRespectsDimensions(): void
    {
        $output = $this->capture(fn() => Icon\mask('home.svg', '32px', '32px'));
        $this->assertStringContainsString('width="32px"', $output);
        $this->assertStringContainsString('height="32px"', $output);
    }

    public function testDefaultDimensions(): void
    {
        $output = $this->capture(fn() => Icon\mask('home.svg'));
        $this->assertStringContainsString('width="24px"', $output);
        $this->assertStringContainsString('height="24px"', $output);
    }

    public function testNestedPath(): void
    {
        $output = $this->capture(fn() => Icon\mask('badges/admin.svg'));
        $this->assertStringContainsString("url('/assets/icons/badges/admin.svg')", $output);
    }

    public function testMissingIconEmitsNothing(): void
    {
        $output = $this->capture(fn() => Icon\mask('nonexistent.svg'));
        $this->assertSame('', $output);
    }

    public function testRemoteIconRendersPublicUrl(): void
    {
        $this->placeRemoteIcon('mdi:home', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=""/></svg>');

        $output = $this->capture(fn() => Icon\mask('mdi:home'));
        $this->assertStringContainsString('mask-image:', $output);
        $this->assertStringContainsString("url('/icons/mdi:home.svg')", $output);
    }

    public function testNoRemotePathEmitsNothing(): void
    {
        IconContext::reset();
        Icon\init_local(storePath: $this->localPath, servePath: '/assets/icons');

        $output = $this->capture(fn() => Icon\mask('mdi:home'));
        $this->assertSame('', $output);
    }
}
