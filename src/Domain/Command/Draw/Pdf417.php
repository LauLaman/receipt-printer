<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

final readonly class Pdf417 implements DrawCommand
{
    public function __construct(
        public string $data,
        public int $ecc = 2,
        public int $moduleX = 2,
        public int $aspect = 3,
        public int $sizeMode = 0, // 0 = USE_LIMITS
        public int $verticalWeight = 1,
        public int $horizontalWeight = 2
    ) {
    }
}