<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Layout;

use LauLaman\ReceiptPrinter\Domain\Command\LeafCommand;

final readonly class Separator implements LayoutCommand, LeafCommand
{
    public function __construct(
        public string $char = '-' // Default character
    ) {}
}
