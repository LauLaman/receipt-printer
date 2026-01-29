<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Layout;

use LauLaman\ReceiptPrinter\Domain\Command\AbstractContainerCommand;

final readonly class UpsideDown extends AbstractContainerCommand implements LayoutCommand
{

}