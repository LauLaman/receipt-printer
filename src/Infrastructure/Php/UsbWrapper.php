<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Php;

class UsbWrapper
{
    public function open(string $device)
    {
        return fopen($device, 'wb');
    }

    public function write($handle, string $data): int|false
    {
        return fwrite($handle, $data);
    }

    public function flush($handle): bool
    {
        return fflush($handle);
    }

    public function close($handle): bool
    {
        return fclose($handle);
    }

    public function setBlocking($handle, bool $blocking): bool
    {
        return stream_set_blocking($handle, $blocking);
    }
}
