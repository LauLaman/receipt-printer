<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Transformer;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;

interface PrinterTransformer
{
    public function supports(PrinterModel $model): bool;

    /**
     * @param PrintSettings $printSettings
     * @param Command ...$commands
     * @return string
     */
    public function transform(PrintSettings $printSettings, Command ...$commands): string;
}