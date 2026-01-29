<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Transformer;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;

interface CommandTransformer
{
    public function supports(Command $command): bool;

    /**
     * @param Command $command
     * @param callable $transformChildren A callable to transform nested commands: fn(ContainerCommand $command): string
     * @param callable $normalizeText A callable to transform normalize text based on the selected CodePage: fn(string $text): string
     * @param PrintSettings $printSettings An object containing all print settings
     */
    public function transform(
        Command $command,
        callable $transformChildren,
        callable $normalizeText,
        PrintSettings $printSettings,
    ): string;
}
