<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Enum;

enum PrinterModel
{
    case STAR_MC_PRINT2;
    case STAR_MC_PRINT3;
    case STAR_TSP650_II;

    public function getCharsPerLine(PaperWidth $paper): int
    {
        return match($this) {
            self::STAR_MC_PRINT2 => match($paper) {
                PaperWidth::SMALL => 32,
            },
            self::STAR_MC_PRINT3 => match($paper) {
                PaperWidth::SMALL => 33,
                PaperWidth::LARGE => 48,
            },
            self::STAR_TSP650_II => match($paper) {
                PaperWidth::SMALL => 33,
                PaperWidth::LARGE => 48,
            },
            default => throw new \LogicException('Unsupported printer model'),
        };
    }
}
