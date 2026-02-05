<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Enum;

enum PaperWidth
{
    case SMALL;
    case LARGE;

    public function getMM(): int
    {
        return match ($this) {
            self::SMALL => 58,
            self::LARGE => 80,
        };
    }

    public function getInches(): float
    {
        return $this->getMM() / 25.4;
    }
}
