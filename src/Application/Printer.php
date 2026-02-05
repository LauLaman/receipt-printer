<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Contract\PrinterDriverInterface;
use LauLaman\ReceiptPrinter\Domain\Contract\PrinterTransportInterface;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrintSetting\PrintSetting;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\PrinterSettings;
use LauLaman\ReceiptPrinter\Domain\Receipt;

final readonly class Printer
{
    public function __construct(
        private PrinterSettings           $settings,
        private PrinterDriverInterface    $driver,
        private PrinterTransportInterface $transport
    ) {}

    public function print(Receipt $receipt): void
    {
        $this->send(
            $receipt->getSettings(),
            ...$receipt->getCommands()
        );
    }

    /**
     * Send commands to the printer with optional settings.
     *
     * @param PrintSetting[] $settings
     * @param Command ...$commands
     */
    public function send(array $settings, Command ...$commands): void
    {
        $printSettings = new PrintSettings();
        $printSettings->add(...$settings);

        $this->settings->setPrintSettings($printSettings);

        $bytes = $this->driver->encode($this->settings, ...$commands);

        $this->transport->write($bytes);
    }
}

