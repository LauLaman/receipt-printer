<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;


final readonly class GS1DataBarExpanded implements DrawCommand
{
    public function __construct(
        public string $data
    ) {}
}
