<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\Sound\Buzzer;
use LauLaman\ReceiptPrinter\Domain\Command\Sound\MelodySpeaker;
use LauLaman\ReceiptPrinter\Domain\Command\Sound\SoundCommand;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\BuzzerPattern;

class SoundTransformer implements CommandTransformer
{
    public function supports(Command $command): bool
    {
        return $command instanceof SoundCommand;
    }

    public function transform(
        Command $command,
        callable $transformChildren,
        callable $normalizeText,
        PrintSettings $printSettings
    ): string {
        return match (true) {
            $command instanceof Buzzer => $this->buzzer($command),
            $command instanceof MelodySpeaker => $this->melodySpeaker($command),
            default => throw new \LogicException('Unsupported sound command ' . $command::class),
        };
    }

    private function buzzer(Buzzer $command): string
    {
        if ($command->pattern === BuzzerPattern::SIMPLE) {
            return "\x07";
        }

        $onTime  = max(1, min(255, (int)($command->onTimeMs / 50)));
        $offTime = max(0, min(255, (int)($command->offTimeMs / 50)));

        return "\x1B\x07" . chr($onTime) . chr($offTime) . "\x07";
    }

    private function melodySpeaker(MelodySpeaker $command): string
    {
        if (empty($command->notes)) {
            throw new \LogicException('MelodySpeaker command must have at least one note.');
        }

        $noteCount = count($command->notes);
        $repeat = max(1, min(255, $command->repeat));

        $data = "\x1B\x1D\x73\x52\x00" . chr($noteCount) . chr($repeat) . "\x00\x00";

        foreach ($command->notes as $note) {
            $noteNumber = max(0, min(127, $note['pitch']));
            $duration   = max(1, min(255, (int)($note['duration'] / 100)));
            $volume     = max(0, min(15, $note['volume']));
            $data .= chr($noteNumber) . chr($duration) . chr($volume);
        }

        return $data;
    }
}
