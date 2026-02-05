<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Contract;

use LauLaman\ReceiptPrinter\Domain\PrinterSettings;

interface TextNormalizerInterface
{
    public function normalize(string $text, PrinterSettings $printerSettings): string;
}