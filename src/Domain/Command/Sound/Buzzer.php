<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Sound;

use LauLaman\ReceiptPrinter\Domain\Enum\BuzzerPattern;

final readonly class Buzzer implements SoundCommand
{
    public function __construct(
        public readonly BuzzerPattern $pattern = BuzzerPattern::SIMPLE,
        public readonly int $onTimeMs = 100,
        public readonly int $offTimeMs = 0,
    ) {
    }
}