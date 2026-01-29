<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Infrastructure\Php\SocketWrapper;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\BluetoothTransport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BluetoothTransportTest extends TestCase
{
    #[Test]
    public function itWritesSuccessfully(): void
    {
        $socket = tmpfile();

        $wrapper = $this->createMock(SocketWrapper::class);
        $wrapper->method('open')->willReturn($socket);

        $calls = [];
        $wrapper->method('write')->willReturnCallback(function ($s, $data) use (&$calls) {
            $calls[] = $data;
            return strlen($data); // simulate full write
        });

        $wrapper->method('flush')->willReturnCallback(function ($s) {
            // do nothing
        });

        $transport = new BluetoothTransport('00:11:22:33:44:55', 1, $wrapper);

        $transport->write('Hello');
        $transport->write('World');

        // assert both writes were called in order
        $this->assertSame(['Hello', 'World'], $calls);
    }

    #[Test]
    public function itThrowsIfWriteFails(): void
    {
        $socket = tmpfile();

        $wrapper = $this->createMock(SocketWrapper::class);
        $wrapper->method('open')->willReturn($socket);
        $wrapper->method('write')->with($socket, 'Hello')->willReturn(2); // simulate partial write

        $transport = new BluetoothTransport('00:11:22:33:44:55', 1, $wrapper);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to write all data');

        $transport->write('Hello');
    }

    #[Test]
    public function itThrowsIfConnectionFails(): void
    {
        $wrapper = $this->createMock(SocketWrapper::class);
        $wrapper->method('open')->willReturn(false);

        $transport = new BluetoothTransport('00:11:22:33:44:55', 1, $wrapper);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Bluetooth connection failed');

        $transport->write('Hello');
    }

    #[Test]
    public function destructorClosesSocket(): void
    {
        $socket = tmpfile();

        $wrapper = $this->createMock(SocketWrapper::class);
        $wrapper->method('open')->willReturn($socket);
        $wrapper->expects($this->once())->method('close')->with($socket);

        $transport = new BluetoothTransport('00:11:22:33:44:55', 1, $wrapper);

        // Manually set the socket to simulate open()
        $reflection = new \ReflectionProperty(BluetoothTransport::class, 'socket');
        $reflection->setAccessible(true);
        $reflection->setValue($transport, $socket);

        unset($transport); // triggers __destruct()
        gc_collect_cycles();
    }

}
