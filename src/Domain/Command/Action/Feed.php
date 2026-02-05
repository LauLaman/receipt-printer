<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Action;

final readonly class Feed implements ActionCommand
{
    public function __construct(public int $lines = 1) {}
}