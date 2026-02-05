<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Domain\Contract\PrinterTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Php\UsbWrapper;

final class UsbTransportInterface implements PrinterTransportInterface
{
    private $handle;

    public function __construct(
        private readonly string $device = '/dev/usb/lp0',
        private readonly UsbWrapper $wrapper = new UsbWrapper()
    ) {
    }

    private function open(): void
    {
        if (is_resource($this->handle)) {
            return;
        }

        $this->handle = $this->wrapper->open($this->device);

        if (!$this->handle) {
            throw new \RuntimeException("Failed to open USB device: {$this->device}");
        }

        $this->wrapper->setBlocking($this->handle, true);
    }

    public function write(string $data): void
    {
        $this->open();

        $bytesWritten = $this->wrapper->write($this->handle, $data);

        if ($bytesWritten === false || $bytesWritten !== \strlen($data)) {
            throw new \RuntimeException('Failed to write all data to USB device.');
        }

        $this->wrapper->flush($this->handle);
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            $this->wrapper->close($this->handle);
        }
    }
}
