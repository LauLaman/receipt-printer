<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Application\PrinterFactory;
use LauLaman\ReceiptPrinter\Application\Printer;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Domain\Transformer\PrinterTransformer;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\BluetoothTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\CupsTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\IppTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\SocketTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\UsbTransport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrinterFactoryTest extends TestCase
{
    #[Test]
    public function itCreatesAPrinterFactory(): void
    {
        $factory = PrinterFactory::create();
        $this->assertInstanceOf(PrinterFactory::class, $factory);
    }

    #[Test]
    public function itBuildsAPrinterWithSocketTransport(): void
    {
        $factory = PrinterFactory::create();
        $printer = $factory->socket(
            PrinterModel::STAR_MC_PRINT2,
            PaperWidth::SMALL,
            '127.0.0.1',
            9100
        );

        $this->assertInstanceOf(Printer::class, $printer);

        $transport = (new \ReflectionProperty(Printer::class, 'transport'))->getValue($printer);
        $this->assertInstanceOf(SocketTransport::class, $transport);
    }

    #[Test]
    public function itBuildsAPrinterWithUsbTransport(): void
    {
        $factory = PrinterFactory::create();
        $printer = $factory->usb(
            PrinterModel::STAR_MC_PRINT2,
            PaperWidth::SMALL,
            '/dev/usb/lp0'
        );

        $this->assertInstanceOf(Printer::class, $printer);

        $transport = (new \ReflectionProperty(Printer::class, 'transport'))->getValue($printer);
        $this->assertInstanceOf(UsbTransport::class, $transport);
    }

    #[Test]
    public function itBuildsAPrinterWithCupsTransport(): void
    {
        $factory = PrinterFactory::create();
        $printer = $factory->cups(
            PrinterModel::STAR_MC_PRINT2,
            PaperWidth::SMALL,
            'mC-Print2'
        );

        $this->assertInstanceOf(Printer::class, $printer);

        $transport = (new \ReflectionProperty(Printer::class, 'transport'))->getValue($printer);
        $this->assertInstanceOf(CupsTransport::class, $transport);
    }

    #[Test]
    public function itBuildsAPrinterWithBluetoothTransport(): void
    {
        $factory = PrinterFactory::create();
        $printer = $factory->bluetooth(
            PrinterModel::STAR_MC_PRINT2,
            PaperWidth::SMALL,
            '00:11:22:33:44:55',
            1
        );

        $this->assertInstanceOf(Printer::class, $printer);

        $transport = (new \ReflectionProperty(Printer::class, 'transport'))->getValue($printer);
        $this->assertInstanceOf(BluetoothTransport::class, $transport);
    }


    #[Test]
    public function itBuildsAPrinterWithIppTransport(): void
    {
        $factory = PrinterFactory::create();

        $printer = $factory->Ipp(
            PrinterModel::STAR_MC_PRINT2,
            PaperWidth::SMALL,
            'ipp://printer.local'
        );

        $this->assertInstanceOf(Printer::class, $printer);

        $transport = (new \ReflectionProperty(Printer::class, 'transport'))->getValue($printer);
        $this->assertInstanceOf(IppTransport::class, $transport);
    }


    #[Test]
    public function itThrowsIfNoTransformerSupportsTheModel(): void
    {
        // Create a mock transformer that does NOT support the model
        $mockTransformer = $this->createMock(PrinterTransformer::class);
        $mockTransformer->method('supports')->willReturn(false);

        // Create a factory with the mock transformer injected
        $factoryReflection = new \ReflectionClass(PrinterFactory::class);
        $factory = $factoryReflection->newInstanceWithoutConstructor();

        $prop = $factoryReflection->getProperty('printerTransformers');
        $prop->setAccessible(true);
        $prop->setValue($factory, [$mockTransformer]);

        // Expect LogicException when calling a builder method (usb)
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/No transformer implemented/');

        $factory->usb(PrinterModel::STAR_MC_PRINT2, PaperWidth::SMALL);
    }
}
