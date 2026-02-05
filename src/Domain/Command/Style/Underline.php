<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Style;

use LauLaman\ReceiptPrinter\Domain\Command\AbstractContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\LayoutCommand;

final readonly class Underline extends AbstractContainerCommand implements StyleCommand
{
}
