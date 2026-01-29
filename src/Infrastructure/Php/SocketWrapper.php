<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Php;

class SocketWrapper
{
    public function open(string $uri, int &$errno, string &$errstr, float $timeout)
    {
        return @stream_socket_client($uri, $errno, $errstr, $timeout);

        return @fsockopen($host, $port, $errno, $errstr, $timeout);
    }

    public function write($socket, string $data): int|false
    {
        return fwrite($socket, $data);
    }

    public function flush($socket): void
    {
        fflush($socket);
    }

    public function close($socket): void
    {
        fclose($socket);
    }
}
