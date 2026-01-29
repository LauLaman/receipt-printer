<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Enum;

enum PrinterSpeed
{
    case HIGH;
    case MEDIUM;
    case LOW;
}
