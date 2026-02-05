<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\PrinterSetting\SupportedFont;

final readonly class CharacterSize
{
    public function __construct(
        public int $width,
        public int $height
    ) {
    }
}