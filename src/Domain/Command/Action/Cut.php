<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Action;

final readonly class Cut implements ActionCommand
{
    public function __construct(
        public bool $feed = true,
        public bool $partial = false
    ) {
    }
}