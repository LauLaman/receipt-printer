<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Paper;

final readonly class Cut implements PaperCommand
{
    public function __construct(
        public bool $feed = true,
        public bool $partial = false
    ) {
    }
}