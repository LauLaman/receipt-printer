<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Domain\Contract\PrinterTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Php\SocketWrapper;

final class SocketTransportInterface implements PrinterTransportInterface
{
    private $socket;

    public function __construct(
        private readonly string $host,
        private readonly int $port = 9100,
        private readonly SocketWrapper $wrapper = new SocketWrapper()
    ) {
    }

    private function open(): void
    {
        if (is_resource($this->socket)) {
            return;
        }

        $errno  = 0;
        $errstr = '';

        $this->socket = $this->wrapper->open(
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            30
        );

        if (!$this->socket) {
            throw new \RuntimeException(
                "Socket connection failed: {$errstr}",
                $errno
            );
        }
    }

    public function write(string $data): void
    {
        $this->open();

        $bytesWritten = $this->wrapper->write($this->socket, $data);
        if ($bytesWritten === false || $bytesWritten !== \strlen($data)) {
            throw new \RuntimeException('Failed to write all data to socket.');
        }

        $this->wrapper->flush($this->socket);
    }

    public function __destruct()
    {
        if (is_resource($this->socket)) {
            $this->wrapper->close($this->socket);
        }
    }
}

