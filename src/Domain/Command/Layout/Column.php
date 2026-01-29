<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Layout;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\ContainerCommand;

final readonly class Column implements LayoutCommand
{
    /**
     * @param Command[] $leftChildren
     * @param Command[] $rightChildren
     */
    public function __construct(
        public array $leftChildren,
        public array $rightChildren,
        public ?string $spacer = ' '
    ) {
    }
}
