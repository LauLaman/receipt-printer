<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command;

abstract readonly class AbstractContainerCommand implements ContainerCommand
{
    protected array $children;

    public function __construct(
        Command ...$children
    ) {
        $this->children = $children;
    }

    public function children(): array
    {
        return $this->children;
    }
}
