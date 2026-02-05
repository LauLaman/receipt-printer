<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Infrastructure\Php\UsbWrapper;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\UsbTransportInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UsbTransportTest extends TestCase
{
    public function testCanInstantiateUsbTransport(): void
    {
        $transport = new UsbTransportInterface('/dev/test');
        $this->assertInstanceOf(UsbTransportInterface::class, $transport);
    }

    #[Test]
    public function testWriteSendsDataToUsbDevice(): void
    {
        $handle = fopen('php://memory', 'wb');

        $wrapper = $this->createMock(UsbWrapper::class);

        // open() called once
        $wrapper->expects($this->once())
            ->method('open')
            ->with('/dev/test')
            ->willReturn($handle);

        // setBlocking() called once
        $wrapper->expects($this->once())
            ->method('setBlocking')
            ->with($handle, true);

        // Track write calls manually
        $calls = [];
        $wrapper->method('write')->willReturnCallback(function ($h, $data) use (&$calls) {
            $calls[] = $data;
            return strlen($data); // simulate full write
        });

        // flush() called each time write happens
        $wrapper->method('flush')->willReturnCallback(fn($h) => true);

        $transport = new UsbTransportInterface('/dev/test', $wrapper);

        $transport->write('HELLO');
        $transport->write('WORLD');

        // Verify that both writes were sent in order
        $this->assertSame(['HELLO', 'WORLD'], $calls);
    }


    public function testWriteThrowsWhenOpenFails(): void
    {
        $wrapper = $this->createMock(UsbWrapper::class);

        $wrapper->expects($this->once())
            ->method('open')
            ->willReturn(false);

        $transport = new UsbTransportInterface('/dev/test', $wrapper);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to open USB device');

        $transport->write('HELLO');
    }

    public function testWriteThrowsWhenWriteFails(): void
    {
        $handle = fopen('php://memory', 'wb');

        $wrapper = $this->createMock(UsbWrapper::class);

        $wrapper->method('open')->willReturn($handle);
        $wrapper->method('setBlocking');

        $wrapper->expects($this->once())
            ->method('write')
            ->willReturn(false);

        $transport = new UsbTransportInterface('/dev/test', $wrapper);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to write all data to USB device.');

        $transport->write('HELLO');
    }
}
