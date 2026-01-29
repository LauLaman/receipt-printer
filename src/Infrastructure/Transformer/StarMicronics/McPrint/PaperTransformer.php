<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\Cut;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\Feed;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\OpenDrawer;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\PaperCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\PrintDensity;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\PrintSpeed;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\FontType;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterSpeed;

class PaperTransformer implements CommandTransformer
{
    public function supports(Command $command): bool
    {
        return $command instanceof PaperCommand;
    }

    public function transform(
        Command $command,
        callable $transformChildren,
        callable $normalizeText,
        PrintSettings $printSettings
    ): string {
        return match (true) {
            $command instanceof Feed => str_repeat("\n", $command->lines),
            $command instanceof Cut => $this->cut($command),
            $command instanceof OpenDrawer => "\x07",
            $command instanceof PrintDensity => $this->printDensity($command),
            $command instanceof PrintSpeed => $this->printSpeed($command),
            default => throw new \LogicException('Unsupported paper command ' . $command::class),
        };
    }

    private function printDensity(PrintDensity $command): string
    {
        $densityByte = max(0, min(6, $command->density + 3));
        return "\x1B\x1E\x64" . chr($densityByte);
    }

    private function printSpeed(PrintSpeed $command): string
    {
        $speed = match($command->speed) {
            PrinterSpeed::LOW => 0,
            PrinterSpeed::MEDIUM => 1,
            PrinterSpeed::HIGH => 2,
        };

        return "\x1B\x1E\x72" . chr($speed);
    }

    private function cut(Cut $command): string
    {
        $data = '';

        if ($command->feed) {
            $data .= str_repeat("\n", 4);
        }

        $data .= $command->partial ? "\x1B\x64\x03" : "\x1B\x64\x02";

        return $data;
    }
}
