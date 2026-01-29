<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Infrastructure\Transport\SocketTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Php\SocketWrapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SocketTransportTest extends TestCase
{
    #[Test]
    public function writeSendsDataToSocketTwice(): void
    {
        $mockWrapper = $this->createMock(SocketWrapper::class);
        $mockSocket  = fopen('php://memory', 'r+');

        // open() called once
        $mockWrapper->expects($this->once())
            ->method('open')
            ->willReturnCallback(function ($uri, int &$errno, string &$errstr, $timeout) use ($mockSocket) {
                $this->assertSame("tcp://localhost:9100", $uri);
                $errno = 0;
                $errstr = '';
                return $mockSocket;
            });

        // Track all write calls
        $calls = [];
        $mockWrapper->method('write')->willReturnCallback(function ($socket, $data) use (&$calls) {
            $calls[] = $data;
            return strlen($data); // simulate full write
        });

        // flush() can also be called multiple times
        $mockWrapper->method('flush')->willReturnCallback(fn($socket) => null);

        $transport = new SocketTransport('localhost', 9100, $mockWrapper);

        $transport->write('Hello');
        $transport->write('Socket');

        // assert both writes were sent in order
        $this->assertSame(['Hello', 'Socket'], $calls);
    }

    #[Test]
    public function writeThrowsWhenWriteFails(): void
    {
        $mockWrapper = $this->createMock(SocketWrapper::class);
        $mockSocket  = fopen('php://memory', 'r+');

        $mockWrapper->expects($this->once())
            ->method('open')
            ->willReturn($mockSocket);

        $mockWrapper->expects($this->once())
            ->method('write')
            ->with($mockSocket, 'fail')
            ->willReturn(false);

        $transport = new SocketTransport('localhost', 9100, $mockWrapper);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to write all data to socket.');

        $transport->write('fail');
    }

    #[Test]
    public function openThrowsWhenConnectionFails(): void
    {
        $mockWrapper = $this->createMock(SocketWrapper::class);

        // Simulate connection failure
        $mockWrapper->expects($this->once())
            ->method('open')
            ->willReturn(false);

        $transport = new SocketTransport('localhost', 9100, $mockWrapper);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Socket connection failed/');

        // Trigger open() indirectly via write()
        $transport->write('Hello');
    }

    #[Test]
    public function destructorClosesSocket(): void
    {
        $mockWrapper = $this->createMock(SocketWrapper::class);
        $mockSocket  = fopen('php://memory', 'r+');

        $mockWrapper->expects($this->once())
            ->method('close')
            ->with($mockSocket);

        $transport = new SocketTransport('localhost', 9100, $mockWrapper);

        // Manually inject the socket for the destructor
        $ref = new \ReflectionProperty(SocketTransport::class, 'socket');
        $ref->setAccessible(true);
        $ref->setValue($transport, $mockSocket);

        unset($transport); // triggers __destruct()
        gc_collect_cycles();
    }
}
