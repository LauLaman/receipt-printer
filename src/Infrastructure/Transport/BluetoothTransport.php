<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Domain\Transport\PrinterTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Php\SocketWrapper;

final class BluetoothTransport implements PrinterTransport
{
    private $socket;

    public function __construct(
        private readonly string $address,
        private readonly int $channel = 1,
        private readonly SocketWrapper $socketWrapper = new SocketWrapper()
    ) {
        // lazy open
    }

    private function open(): void
    {
        if (is_resource($this->socket)) {
            return;
        }

        $errno  = 0;
        $errstr = '';

        $this->socket = $this->socketWrapper->open(
            "bt://{$this->address}:{$this->channel}",
            $errno,
            $errstr,
            30
        );

        if (!$this->socket) {
            throw new \RuntimeException("Bluetooth connection failed: {$errstr}", $errno);
        }
    }

    public function write(string $data): void
    {
        $this->open();

        $bytesWritten = $this->socketWrapper->write($this->socket, $data);
        if ($bytesWritten === false || $bytesWritten !== strlen($data)) {
            throw new \RuntimeException('Failed to write all data to Bluetooth device.');
        }

        $this->socketWrapper->flush($this->socket);
    }

    public function __destruct()
    {
        if (is_resource($this->socket)) {
            $this->socketWrapper->close($this->socket);
        }
    }
}
