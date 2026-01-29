<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Infrastructure\Php\ProcessRunner;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\CupsTransport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CupsTransportTest extends TestCase
{
    #[Test]
    public function writeSendsDataToCups(): void
    {
        $mockRunner = $this->createMock(ProcessRunner::class);

        $mockRunner->expects($this->once())
            ->method('run')
            ->willReturnCallback(function(string $command, array $descriptors, array &$pipes) {
                $pipes[0] = fopen('php://memory', 'r+');
                $pipes[1] = fopen('php://memory', 'r+');
                $pipes[2] = fopen('php://memory', 'r+');

                return fopen('php://memory', 'r+'); // dummy process
            });

        $mockRunner->expects($this->once())
            ->method('close')
            ->with($this->isType('resource'));

        $transport = new CupsTransport('TestPrinter', $mockRunner);

        // Should not throw
        $transport->write('Hello World');
    }

    #[Test]
    public function writeThrowsWhenProcessFails(): void
    {
        $mockRunner = $this->createMock(ProcessRunner::class);
        $mockRunner->expects($this->once())
            ->method('run')
            ->willReturn(false); // simulate failure

        $transport = new CupsTransport('TestPrinter', $mockRunner);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to open CUPS printer');

        $transport->write('Hello World');
    }
}
