<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Layout;

use LauLaman\ReceiptPrinter\Domain\Command\AbstractContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Enum\FontType;

final readonly class Font extends AbstractContainerCommand implements LayoutCommand
{
    public function __construct(
        public FontType $type,
        Command ...$children
    ) {
        parent::__construct(...$children);
    }
}
