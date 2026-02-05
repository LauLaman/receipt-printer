<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

final readonly class Logo implements DrawCommand
{
    public function __construct(
        public int $number,
        public int $scale = 1
    ) {}
}