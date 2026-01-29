<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterSpeed;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\PaperTransformer;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\{
    Feed, Cut, OpenDrawer, PrintDensity, PrintSpeed
};
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaperTransformerTest extends TestCase
{
    private PaperTransformer $transformer;
    private PrintSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new PaperTransformer();
        $this->settings = new PrintSettings();
    }

    #[Test]
    public function itTransformsFeed(): void
    {
        $feed = new Feed(3);
        $result = $this->transformer->transform($feed, fn($c) => '', fn($t) => $t, $this->settings);
        $this->assertSame("\n\n\n", $result);
    }

    #[Test]
    public function itTransformsFullCut(): void
    {
        $cut = new Cut(partial: false, feed: false);
        $result = $this->transformer->transform($cut, fn($c) => '', fn($t) => $t, $this->settings);
        $this->assertSame("\x1B\x64\x02", $result);
    }

    #[Test]
    public function itTransformsPartialCutWithFeed(): void
    {
        $cut = new Cut(partial: true, feed: true);
        $result = $this->transformer->transform($cut, fn($c) => '', fn($t) => $t, $this->settings);
        $this->assertSame(str_repeat("\n", 4) . "\x1B\x64\x03", $result);
    }

    #[Test]
    public function itTransformsOpenDrawer(): void
    {
        $drawer = new OpenDrawer();
        $result = $this->transformer->transform($drawer, fn($c) => '', fn($t) => $t, $this->settings);
        $this->assertSame("\x07", $result);
    }

    #[Test]
    #[DataProvider('printDensityProvider')]
    public function itTransformsPrintDensity(int $input, string $expected): void
    {
        $density = new PrintDensity($input);
        $result = $this->transformer->transform($density, fn($c) => '', fn($t) => $t, $this->settings);
        $this->assertSame($expected, $result);
    }

    public static function printDensityProvider(): array
    {
        return [
            [-5, "\x1B\x1E\x64\x00"],
            [-4, "\x1B\x1E\x64\x00"],
            [-3, "\x1B\x1E\x64\x00"],
            [-2, "\x1B\x1E\x64\x01"],
            [-1, "\x1B\x1E\x64\x02"],
            [0, "\x1B\x1E\x64\x03"],
            [1, "\x1B\x1E\x64\x04"],
            [2, "\x1B\x1E\x64\x05"],
            [3, "\x1B\x1E\x64\x06"],
            [4, "\x1B\x1E\x64\x06"],
            [5, "\x1B\x1E\x64\x06"],
            [6, "\x1B\x1E\x64\x06"],
            [7, "\x1B\x1E\x64\x06"],
            [8, "\x1B\x1E\x64\x06"],
            [9, "\x1B\x1E\x64\x06"],
            [10, "\x1B\x1E\x64\x06"],
        ];
    }

    #[Test]
    #[DataProvider('printSpeedProvider')]
    public function itTransformsPrintSpeed(PrinterSpeed $speedEnum, string $expected): void
    {
        $speed = new PrintSpeed($speedEnum);
        $result = $this->transformer->transform($speed, fn($c) => '', fn($t) => $t, $this->settings);
        $this->assertSame($expected, $result);
    }

    public static function printSpeedProvider(): array
    {
        return [
            [PrinterSpeed::LOW, "\x1B\x1E\x72\x00"],
            [PrinterSpeed::MEDIUM, "\x1B\x1E\x72\x01"],
            [PrinterSpeed::HIGH, "\x1B\x1E\x72\x02"],
        ];
    }

    #[Test]
    public function itThrowsForUnsupportedCommand(): void
    {
        $this->expectException(\LogicException::class);

        $unsupported = $this->createMock(Command::class);
        $this->transformer->transform($unsupported, fn($c) => '', fn($t) => $t, $this->settings);
    }
}
