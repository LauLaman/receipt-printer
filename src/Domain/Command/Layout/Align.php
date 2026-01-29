<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Layout;

use LauLaman\ReceiptPrinter\Domain\Command\AbstractContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;

final readonly class Align extends AbstractContainerCommand implements LayoutCommand
{
    public function __construct(
        public Alignment $mode,
        Command ...$children
    ) {
        parent::__construct(...$children);
    }
}