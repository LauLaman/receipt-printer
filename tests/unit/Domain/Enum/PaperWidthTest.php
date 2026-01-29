<?php

declare(strict_types=1);

namespace Test\Unit\LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;

use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaperWidthTest extends TestCase
{
    #[Test]
    public function itReturnsTheCorrectMillimetersForEachPaperWidth(): void
    {
        $this->assertSame(58, PaperWidth::SMALL->getMM());
        $this->assertSame(80, PaperWidth::LARGE->getMM());
    }

    #[Test]
    #[DataProvider('paperWidthProvider')]
    public function itReturnsCorrectMillimetersViaDataProvider(PaperWidth $paperWidth, int $expectedMm): void
    {
        $this->assertSame($expectedMm, $paperWidth->getMM());
    }

    public static function paperWidthProvider(): array
    {
        return [
            [PaperWidth::SMALL, 58],
            [PaperWidth::LARGE, 80],
        ];
    }
}