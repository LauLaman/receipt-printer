<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command;

interface ContainerCommand extends Command
{
    /** @return Command[] */
    public function children(): array;
}
