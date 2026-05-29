<?php

use Wilaak\Iconify\_IconCtx as IconContext;
use Wilaak\Iconify as Icon;

class ErrorHandlingTest extends RenderTestCase
{
    public function testNotCalledWhenIconExists(): void
    {
        $errors = [];
        Icon\on_error(function (string $icon, string $_reason) use (&$errors) {
            $errors[] = $icon;
        });

        $this->capture(fn() => Icon\mask('home.svg'));
        $this->assertSame([], $errors);
    }

    public function testCalledWhenLocalIconMissing(): void
    {
        $errors = [];
        Icon\on_error(function (string $icon, string $reason) use (&$errors) {
            $errors[] = [$icon, $reason];
        });

        $this->capture(fn() => Icon\mask('badges/nonexistent.svg'));
        $this->assertSame([['badges/nonexistent.svg', 'Icon file not found']], $errors);
    }

    public function testCalledWhenLocalIconMissingViaInline(): void
    {
        $errors = [];
        Icon\on_error(function (string $icon, string $reason) use (&$errors) {
            $errors[] = [$icon, $reason];
        });

        $this->capture(fn() => Icon\inline('badges/nonexistent.svg'));
        $this->assertSame([['badges/nonexistent.svg', 'Icon file not found']], $errors);
    }

    public function testCalledWhenApiIconNotFound(): void
    {
        $errors = [];
        Icon\on_error(function (string $icon, string $reason) use (&$errors) {
            $errors[] = [$icon, $reason];
        });

        $this->capture(fn() => Icon\mask('jargon:blorpwizzle'));
        $this->assertSame([['jargon:blorpwizzle', 'Icon not found in API response']], $errors);
    }

    public function testCalledWhenApiRequestFails(): void
    {
        IconContext::get()->apiUrl = 'http://localhost:1';

        $errors = [];
        Icon\on_error(function (string $icon, string $reason) use (&$errors) {
            $errors[] = [$icon, $reason];
        });

        $this->capture(fn() => Icon\mask('jargon:blorpwizzle'));
        $this->assertStringStartsWith('API request failed:', $errors[0][1] ?? '');
    }
}
