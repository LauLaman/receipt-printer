<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Layout;

use LauLaman\ReceiptPrinter\Domain\Command\LeafCommand;

final readonly class Escape implements LeafCommand, LayoutCommand
{
}