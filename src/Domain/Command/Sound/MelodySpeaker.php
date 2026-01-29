<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Sound;

final readonly class MelodySpeaker implements SoundCommand
{
    /**
     * @param array<array{pitch: int, duration: int, volume: int}> $notes
     * @param int $repeat
     */
    public function __construct(
        public readonly array $notes = [],
        public readonly int $repeat = 1,
    ) {
    }
}