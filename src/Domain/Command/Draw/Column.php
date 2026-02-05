<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\LayoutCommand;

final readonly class Column implements DrawCommand
{
    /**
     * @param Command[] $left
     * @param Command[] $right
     * @param Command[] $short
     */
    public function __construct(
        public array   $left,
        public array   $right,
        public ?array $short = null,
        public ?int $indent = null,
        public ?string $spacer = ' '
    ) {
    }
}
