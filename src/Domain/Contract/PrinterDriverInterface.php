<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Contract;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModelInterface;
use LauLaman\ReceiptPrinter\Domain\PrinterSettings;

interface PrinterDriverInterface
{
    public function supports(PrinterModelInterface $model): bool;

    public function encode(PrinterSettings $printerSettings, Command ...$commands): string;
}