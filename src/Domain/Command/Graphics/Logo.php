<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Graphics;

final readonly class Logo implements GraphicsCommand
{
    public function __construct(
        public int $number,
        public int $scale = 1
    ) {}
}