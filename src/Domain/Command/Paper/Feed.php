<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Paper;

final readonly class Feed implements PaperCommand
{
    public function __construct(public int $lines = 1) {}
}