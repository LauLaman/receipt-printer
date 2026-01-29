<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Enum;

enum QRCodeErrorCorrection
{
    case LOW;
    case MEDIUM;
    case QUARTILE;
    case HIGH;
}
