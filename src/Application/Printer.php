<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Receipt;
use LauLaman\ReceiptPrinter\Domain\Settings\PaperWidthSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrinterModelSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\PrinterTransformer;
use LauLaman\ReceiptPrinter\Domain\Transport\PrinterTransport;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;

final readonly class Printer
{
    public function __construct(
        private PrinterModel $model,
        private PaperWidth $paper,
        private PrinterTransformer $transformer,
        private PrinterTransport $transport
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
        $printSettings->add(new PrinterModelSetting($this->model));
        $printSettings->add(new PaperWidthSetting($this->paper, $this->model->getCharsPerLine($this->paper)));
        $printSettings->add(...$settings);

        $bytes = $this->transformer->transform($printSettings, ...$commands);
        $this->transport->write($bytes);
    }
}

