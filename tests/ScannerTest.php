<?php

use PHPUnit\Framework\TestCase;
use function Wilaak\Iconify\Scanner\_scan_file_icons;

class ScannerTest extends TestCase
{
    public function testSimpleImport(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use Wilaak\Iconify;
            Iconify\mask('mdi:home');
            Iconify\inline('heroicons:magnifying-glass');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'heroicons:magnifying-glass'], $icons);
    }

    public function testAliasedImport(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use Wilaak\Iconify as Icon;
            Icon\mask('mdi:home');
            Icon\inline('mdi:account');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account'], $icons);
    }

    public function testGroupedImport(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use Wilaak\{Iconify, SomethingElse};
            Iconify\mask('mdi:home');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home'], $icons);
    }

    public function testGroupedImportWithAlias(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use Wilaak\{Iconify as Icon, SomethingElse};
            Icon\mask('mdi:home');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home'], $icons);
    }

    public function testFunctionImport(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use function Wilaak\Iconify\mask;
            use function Wilaak\Iconify\inline;
            mask('mdi:home');
            inline('mdi:account');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account'], $icons);
    }

    public function testFunctionImportWithAlias(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use function Wilaak\Iconify\mask as iconMask;
            use function Wilaak\Iconify\inline as iconInline;
            iconMask('mdi:home');
            iconInline('mdi:account');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account'], $icons);
    }

    public function testGroupedFunctionImport(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use function Wilaak\Iconify\{mask, inline};
            mask('mdi:home');
            inline('mdi:account');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account'], $icons);
    }

    public function testGroupedFunctionImportWithAliases(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use function Wilaak\Iconify\{mask as iconMask, inline as iconInline};
            iconMask('mdi:home');
            iconInline('mdi:account');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account'], $icons);
    }

    public function testMultilineGroupedFunctionImport(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use function Wilaak\Iconify\{
                mask as iconMask,
                inline as iconInline,
            };
            iconMask('mdi:home');
            iconInline('lucide:rocket');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'lucide:rocket'], $icons);
    }

    public function testFullyQualifiedCall(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            Wilaak\Iconify\mask('mdi:home');
            \Wilaak\Iconify\inline('mdi:account');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account'], $icons);
    }

    public function testNamespaceCaseInsensitivity(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use WILAAK\ICONIFY;
            use function wilaak\iconify\{mask as iconMask, INLINE as iconInline};
            ICONIFY\mask('mdi:home');
            iconMask('mdi:account');
            iconInline('mdi:bell');
            WILAAK\ICONIFY\mask('mdi:star');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account', 'mdi:bell', 'mdi:star'], $icons);
    }

    public function testDoubleQuotedIconStrings(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use Wilaak\Iconify;
            Iconify\mask("mdi:home");
            Iconify\inline("mdi:account");
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home', 'mdi:account'], $icons);
    }

    public function testDeduplication(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use Wilaak\Iconify as Icon;
            Icon\mask('mdi:home');
            Icon\mask('mdi:home');
            Icon\inline('mdi:home');
            PHP);

        $this->assertEqualsCanonicalizing(['mdi:home'], $icons);
    }

    public function testCustomIconsAreIgnored(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            use Wilaak\Iconify as Icon;
            Icon\mask('logo.svg');
            Icon\inline('badges/admin.svg');
            PHP);

        $this->assertSame([], $icons);
    }

    public function testNoImportNoResults(): void
    {
        $icons = _scan_file_icons(<<<'PHP'
            <?php
            mask('mdi:home');
            PHP);

        $this->assertSame([], $icons);
    }
}
