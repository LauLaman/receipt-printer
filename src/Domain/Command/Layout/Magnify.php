<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Layout;

use LauLaman\ReceiptPrinter\Domain\Command\AbstractContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Command;

final readonly class Magnify extends AbstractContainerCommand implements LayoutCommand
{
    public function __construct(
        public int $width,
        public ?int $height = null,
        Command ...$children
    ) {
        parent::__construct(...$children);
    }
}