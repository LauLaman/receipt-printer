<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\GraphicsCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\ImageFile;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\Logo;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\GraphicsTransformer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GraphicsTransformerTest extends TestCase
{
    private GraphicsTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new GraphicsTransformer();
    }

    #[Test]
    public function itSupportsGraphicsCommands(): void
    {
        $graphics = $this->createMock(GraphicsCommand::class);
        $this->assertTrue($this->transformer->supports($graphics));
    }

    #[Test]
    public function itDoesNotSupportOtherCommands(): void
    {
        $command = $this->createMock(Command::class);
        $this->assertFalse($this->transformer->supports($command));
    }

    #[Test]
    public function itGeneratesCorrectSequenceForLogo(): void
    {
        $logo = new Logo(5);
        $expected = "\x1B\x1C\x70" . chr(5) . chr(0);

        $this->assertSame($expected, $this->transformer->logo($logo));
    }

    #[Test]
    public function itThrowsForLogoWithInvalidNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Logo number must be 1–255');

        $logo = new Logo(0);
        $this->transformer->logo($logo);
    }

    #[Test]
    public function itTransformsLogoCommand(): void
    {
        $logo = new Logo(7);

        $result = $this->transformer->transform(
            $logo,
            fn($c) => '',       // transformChildren
            fn($t, $ps) => $t, // normalizeText
            new PrintSettings()
        );

        $this->assertSame("\x1B\x1C\x70" . chr(7) . chr(0), $result);
    }

    #[Test]
    public function itThrowsWhenTransformingImageFile(): void
    {
        $imageFile = new ImageFile('path', 100, 100);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/😭/');

        $this->transformer->transform(
            $imageFile,
            fn($c) => '',
            fn($t, $ps) => $t,
            new PrintSettings()
        );
    }

    #[Test]
    public function itThrowsForUnsupportedCommand(): void
    {
        $dummy = new class implements Command {};
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported barcode command');

        $this->transformer->transform(
            $dummy,
            fn($c) => '',
            fn($t, $ps) => $t,
            new PrintSettings()
        );
    }
}
