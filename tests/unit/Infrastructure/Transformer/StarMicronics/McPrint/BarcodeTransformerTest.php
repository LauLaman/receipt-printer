<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Barcode;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\GS1DataBarExpanded;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Pdf417;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\QRCode;
use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;
use LauLaman\ReceiptPrinter\Domain\Enum\QRCodeErrorCorrection;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\BarcodeTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BarcodeTransformerTest extends TestCase
{
    private BarcodeTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new BarcodeTransformer();
    }

    #[Test]
    public function itSupportsBarcodeAndQrCodeCommands(): void
    {
        $barcode = new Barcode('123', BarcodeType::CODE39);
        $qr = new QRCode('ABC');

        $this->assertTrue($this->transformer->supports($barcode));
        $this->assertTrue($this->transformer->supports($qr));
    }

    #[DataProvider('barcodeProvider')]
    #[Test]
    public function itTransformsBarcodeCorrectly(
        string $data,
        BarcodeType $type,
        int $width,
        int $height,
        bool $hri,
        string $expected
    ): void {
        $cmd = new Barcode($data, $type, $width, $height, $hri);

        $result = $this->transformer->transform(
            $cmd,
            fn($c) => '',
            fn($t, $ps) => $t,
            new PrintSettings()
        );

        $this->assertSame($expected, $result);
    }

    public static function barcodeProvider(): array
    {
        $data = '12345';
        $width = 2;
        $height = 50;

        return [
            'UPC_E with HRI'    => [$data, BarcodeType::UPC_E, $width, $height, true, "\x1B\x62\x00\x02\x02\x32{$data}\x1E\n"],
            'UPC_E without HRI' => [$data, BarcodeType::UPC_E, $width, $height, false, "\x1B\x62\x00\x00\x02\x32{$data}\x1E\n"],
            'UPC_A with HRI'    => [$data, BarcodeType::UPC_A, $width, $height, true, "\x1B\x62\x01\x02\x02\x32{$data}\x1E\n"],
            'UPC_A without HRI' => [$data, BarcodeType::UPC_A, $width, $height, false, "\x1B\x62\x01\x00\x02\x32{$data}\x1E\n"],
            'EAN8 with HRI'     => [$data, BarcodeType::EAN8, $width, $height, true, "\x1B\x62\x02\x02\x02\x32{$data}\x1E\n"],
            'EAN8 without HRI'  => [$data, BarcodeType::EAN8, $width, $height, false, "\x1B\x62\x02\x00\x02\x32{$data}\x1E\n"],
            'EAN13 with HRI'    => [$data, BarcodeType::EAN13, $width, $height, true, "\x1B\x62\x03\x02\x02\x32{$data}\x1E\n"],
            'EAN13 without HRI' => [$data, BarcodeType::EAN13, $width, $height, false, "\x1B\x62\x03\x00\x02\x32{$data}\x1E\n"],
            'CODE39 with HRI'   => [$data, BarcodeType::CODE39, $width, $height, true, "\x1B\x62\x04\x02\x02\x32{$data}\x1E\n"],
            'CODE39 without HRI'=> [$data, BarcodeType::CODE39, $width, $height, false, "\x1B\x62\x04\x00\x02\x32{$data}\x1E\n"],
            'ITF with HRI'      => [$data, BarcodeType::ITF, $width, $height, true, "\x1B\x62\x05\x02\x02\x32{$data}\x1E\n"],
            'ITF without HRI'   => [$data, BarcodeType::ITF, $width, $height, false, "\x1B\x62\x05\x00\x02\x32{$data}\x1E\n"],
            'CODE128 with HRI'  => [$data, BarcodeType::CODE128, $width, $height, true, "\x1B\x62\x06\x02\x02\x32{$data}\x1E\n"],
            'CODE128 without HRI'=> [$data, BarcodeType::CODE128, $width, $height, false, "\x1B\x62\x06\x00\x02\x32{$data}\x1E\n"],
            'CODE93 with HRI'   => [$data, BarcodeType::CODE93, $width, $height, true, "\x1B\x62\x07\x02\x02\x32{$data}\x1E\n"],
            'CODE93 without HRI'=> [$data, BarcodeType::CODE93, $width, $height, false, "\x1B\x62\x07\x00\x02\x32{$data}\x1E\n"],
        ];
    }

    #[Test]
    public function itTransformsQrCode(): void
    {
        $cmd = new QRCode('HELLO', 4, QRCodeErrorCorrection::HIGH);
        $result = $this->transformer->transform(
            $cmd,
            fn($c) => '',
            fn($t, $ps) => $t,
            new PrintSettings()
        );

        $this->assertStringContainsString('HELLO', $result);
        $this->assertStringContainsString("\x1B\x1D\x79\x50", $result);
    }

    #[Test]
    public function itTransformsPdf417(): void
    {
        $cmd = new Pdf417('DATA', 0, 2, 3, 3, 3, 3);
        $result = $this->transformer->transform(
            $cmd,
            fn($c) => '',
            fn($t, $ps) => $t,
            new PrintSettings()
        );

        $this->assertStringContainsString('DATA', $result);
        $this->assertStringStartsWith("\x1B\x1D\x78\x53", $result);
    }

    #[Test]
    public function itThrowsForPdf417InvalidDataLength(): void
    {
        $transformer = new BarcodeTransformer();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('PDF417 data length must be 1–1024 bytes');
        $transformer->transform(new Pdf417(''), fn($c) => '', fn($t) => $t, new PrintSettings());
    }

    #[Test]
    public function itThrowsForPdf417TooLongData(): void
    {
        $transformer = new BarcodeTransformer();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('PDF417 data length must be 1–1024 bytes');
        $transformer->transform(new Pdf417(str_repeat('A', 1025)), fn($c) => '', fn($t) => $t, new PrintSettings());
    }

    #[Test]
    public function itTransformsGs1DataBarExpanded(): void
    {
        $cmd = new GS1DataBarExpanded('123456');
        $result = $this->transformer->transform(
            $cmd,
            fn($c) => '',
            fn($t, $ps) => $t,
            new PrintSettings()
        );

        $this->assertStringContainsString('123456', $result);
        $this->assertStringEndsWith("\n", $result);
    }

    #[Test]
    public function itThrowsForGs1DataBarExpandedInvalidLength(): void
    {
        $transformer = new BarcodeTransformer();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('GS1 Expanded data length invalid (2–255 bytes)');
        $transformer->transform(new GS1DataBarExpanded('A'), fn($c) => '', fn($t) => $t, new PrintSettings());
    }

    #[Test]
    public function itThrowsForUnsupportedCommand(): void
    {
        $cmd = new class implements Command {};

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported barcode command ' . Command::class.'@anonymous');

        $this->transformer->transform(
            $cmd,
            fn($c) => '',
            fn($t, $ps) => $t,
            new PrintSettings()
        );
    }
}
