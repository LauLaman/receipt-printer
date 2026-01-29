<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transport;

use LauLaman\ReceiptPrinter\Infrastructure\Php\ExecRunner;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\IppTransport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IppTransportTest extends TestCase
{
    #[Test]
    public function writeExecutesLprSuccessfully(): void
    {
        $mockRunner = $this->createMock(ExecRunner::class);

        $mockRunner->expects($this->once())
            ->method('run')
            ->with($this->stringContains('lpr -H usb://Star/mC-Print2?serial=12345678901234'))
            ->willReturnCallback(function(string $command, array &$output = null, int &$returnVar = null) {
                $output = ['ok'];
                $returnVar = 0;
            });

        $transport = new IppTransport('12345678901234', $mockRunner);
        $transport->write('Hello Printer');
    }


    #[Test]
    public function writeThrowsWhenExecFails(): void
    {
        // Create a mock for ExecRunner
        $mockRunner = $this->createMock(ExecRunner::class);

        // Configure the mock to simulate a failing command
        $mockRunner->expects($this->once())
            ->method('run')
            ->willReturnCallback(function(string $command, array &$output = null, int &$returnVar = null) {
                $output = ['printer error'];
                $returnVar = 1; // non-zero triggers exception
            });

        $transport = new IppTransport(
            '12345678901234',
            $mockRunner
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Print failed: printer error');

        $transport->write('Hello Printer');
    }
}
