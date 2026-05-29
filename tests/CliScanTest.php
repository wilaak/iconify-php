<?php

use PHPUnit\Framework\TestCase;

class CliScanTest extends TestCase
{
    private string $cacheDir;
    private string $bin;
    private string $fixtures;
    private array $tempDirs = [];

    protected function setUp(): void
    {
        $this->bin      = dirname(__DIR__) . '/bin/iconify-php';
        $this->fixtures = __DIR__ . '/fixtures';
        $this->cacheDir = sys_get_temp_dir() . '/iconify-php-test-' . uniqid();
        mkdir($this->cacheDir, 0755, true);
        $this->tempDirs = [$this->cacheDir];
    }

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            foreach (glob($dir . '/*.svg') ?: [] as $f) unlink($f);
            foreach (glob($dir . '/*.php') ?: [] as $f) unlink($f);
            @rmdir($dir);
        }
    }

    private function runScan(string $srcPath): array
    {
        $cmd = escapeshellarg($this->bin) . ' scan ' . escapeshellarg($this->cacheDir) . ' ' . escapeshellarg($srcPath) . ' 2>&1';
        exec($cmd, $outputLines, $exitCode);
        return [$exitCode, implode("\n", $outputLines)];
    }

    private function prePopulate(array $icons): void
    {
        $stub = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=""/></svg>';
        foreach ($icons as $icon) {
            file_put_contents($this->cacheDir . '/' . $icon . '.svg', $stub);
        }
    }

    private function fixtureDir(string $file): string
    {
        $dir = sys_get_temp_dir() . '/iconify-fixture-' . uniqid();
        mkdir($dir, 0755, true);
        copy($this->fixtures . '/' . $file, $dir . '/' . $file);
        $this->tempDirs[] = $dir;
        return $dir;
    }

    public function testScanReportsCorrectIconCount(): void
    {
        $expected = [
            'mdi:home', 'mdi:account',
            'heroicons:magnifying-glass', 'heroicons:bell',
            'lucide:rocket', 'lucide:star',
            'ph:house', 'ph:user',
            'tabler:home', 'tabler:user',
            'mdi:bell', 'mdi:star', 'mdi:heart', 'mdi:check',
        ];

        $this->prePopulate($expected);

        [$exitCode, $output] = $this->runScan($this->fixtures);

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Found ' . count($expected) . ' unique icon(s)', $output);
        $this->assertStringContainsString('Downloaded: 0  Already present: ' . count($expected), $output);
    }

    public function testScanSimpleImportFixture(): void
    {
        $this->prePopulate(['mdi:home', 'mdi:account']);
        [$exitCode, $output] = $this->runScan($this->fixtureDir('simple_import.php'));
        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Found 2 unique icon(s)', $output);
    }

    public function testScanAliasedImportFixture(): void
    {
        $this->prePopulate(['heroicons:magnifying-glass', 'heroicons:bell']);
        [$exitCode, $output] = $this->runScan($this->fixtureDir('aliased_import.php'));
        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Found 2 unique icon(s)', $output);
    }

    public function testScanFunctionImportFixture(): void
    {
        $this->prePopulate(['lucide:rocket', 'lucide:star']);
        [$exitCode, $output] = $this->runScan($this->fixtureDir('function_import.php'));
        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Found 2 unique icon(s)', $output);
    }

    public function testScanGroupedFunctionImportFixture(): void
    {
        $this->prePopulate(['ph:house', 'ph:user']);
        [$exitCode, $output] = $this->runScan($this->fixtureDir('grouped_function_import.php'));
        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Found 2 unique icon(s)', $output);
    }

    public function testScanFullyQualifiedFixture(): void
    {
        $this->prePopulate(['tabler:home', 'tabler:user']);
        [$exitCode, $output] = $this->runScan($this->fixtureDir('fully_qualified.php'));
        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Found 2 unique icon(s)', $output);
    }

    public function testScanMixedCaseImportFixture(): void
    {
        $this->prePopulate(['mdi:bell', 'mdi:star', 'mdi:heart', 'mdi:check']);
        [$exitCode, $output] = $this->runScan($this->fixtureDir('mixed_case_import.php'));
        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Found 4 unique icon(s)', $output);
    }

    public function testScanEmptyDirectoryExitsCleanly(): void
    {
        $emptyDir = sys_get_temp_dir() . '/iconify-php-empty-' . uniqid();
        mkdir($emptyDir, 0755, true);
        $this->tempDirs[] = $emptyDir;

        [$exitCode, $output] = $this->runScan($emptyDir);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No icon calls found', $output);
    }

    public function testScanNonExistentSourceDirectoryExitsWithError(): void
    {
        [$exitCode, $output] = $this->runScan('/nonexistent/path/that/does/not/exist');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Source directory not found', $output);
    }
}
