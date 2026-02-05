<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

final readonly class Separator implements DrawCommand
{
    public function __construct(
        public string $char = '-' // Default character
    ) {}
}
