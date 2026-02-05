<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Action;

final readonly class PrintDensity implements ActionCommand
{
    public function __construct(
        public int $density = 0, // -3 to +3 (lighter to darker)
    ) {
    }
}
