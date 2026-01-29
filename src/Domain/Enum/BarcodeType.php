<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Enum;

enum BarcodeType
{
    case UPC_E;
    case UPC_A;
    case EAN8;
    case EAN13;
    case CODE39;
    case ITF;
    case CODE128;
    case CODE93;
}
