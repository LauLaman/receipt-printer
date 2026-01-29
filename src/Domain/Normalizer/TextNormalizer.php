<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Normalizer;

use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;

interface TextNormalizer
{
    public function normalize(string $text, PrintSettings $printSettings): string;
}