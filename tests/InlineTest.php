<?php

use Wilaak\Iconify\_IconCtx as IconContext;
use Wilaak\Iconify as Icon;

class InlineTest extends RenderTestCase
{
    public function testEmbedsSvgMarkup(): void
    {
        $output = $this->capture(fn() => Icon\inline('home.svg'));
        $this->assertStringContainsString('<svg ', $output);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $output);
        $this->assertStringContainsString('<path ', $output);
    }

    public function testRespectsDimensions(): void
    {
        $output = $this->capture(fn() => Icon\inline('home.svg', '32px', '32px'));
        $this->assertStringContainsString('width="32px"', $output);
        $this->assertStringContainsString('height="32px"', $output);
    }

    public function testDefaultDimensions(): void
    {
        $output = $this->capture(fn() => Icon\inline('home.svg'));
        $this->assertStringContainsString('width="24px"', $output);
        $this->assertStringContainsString('height="24px"', $output);
    }

    public function testNestedPath(): void
    {
        $output = $this->capture(fn() => Icon\inline('badges/admin.svg'));
        $this->assertStringContainsString('viewBox="0 0 16 16"', $output);
        $this->assertStringContainsString('<circle ', $output);
    }

    public function testMissingIconEmitsNothing(): void
    {
        $output = $this->capture(fn() => Icon\inline('nonexistent.svg'));
        $this->assertSame('', $output);
    }

    public function testRemoteIconEmbedsSvgMarkup(): void
    {
        $this->placeRemoteIcon('mdi:home', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 20H5V3h14v8"/></svg>');

        $output = $this->capture(fn() => Icon\inline('mdi:home'));
        $this->assertStringContainsString('viewBox="0 0 24 24"', $output);
        $this->assertStringContainsString('<path ', $output);
    }

    public function testNoRemotePathEmitsNothing(): void
    {
        IconContext::reset();
        Icon\init_local(storePath: $this->localPath, servePath: '/assets/icons');

        $output = $this->capture(fn() => Icon\inline('mdi:home'));
        $this->assertSame('', $output);
    }
}
