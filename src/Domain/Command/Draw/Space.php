<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

final readonly class Space implements DrawCommand
{
    public function __construct(
        public int $count = 1,
    ) {}
}