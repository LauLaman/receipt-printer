<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

use LauLaman\ReceiptPrinter\Domain\Command\AbstractContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Command;

final readonly class Margin extends AbstractContainerCommand implements DrawCommand
{
    public function __construct(
        public ?int $left = null,
        public ?int $right = null,
        Command ...$children
    ) {
        parent::__construct(...$children);
    }
}
