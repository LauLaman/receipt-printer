<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Settings;

use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;

final class PaperWidthSetting implements PrintSetting
{
    public function __construct(
        public readonly PaperWidth $paperWidth,
        public readonly int $charsPerLine,
        public ?int $charsPerLineMultiplier = 1,
    ) {}
}