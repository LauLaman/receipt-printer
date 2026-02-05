<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Style;

use LauLaman\ReceiptPrinter\Domain\Command\AbstractContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\LayoutCommand;

final readonly class Magnify extends AbstractContainerCommand implements StyleCommand
{
    public function __construct(
        public int $width,
        public ?int $height = null,
        Command ...$children
    ) {
        parent::__construct(...$children);
    }
}