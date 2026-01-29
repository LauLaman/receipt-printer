<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\GraphicsCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\ImageFile;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\Logo;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;

class GraphicsTransformer implements CommandTransformer
{
    public function supports(Command $command): bool
    {
        return $command instanceof GraphicsCommand;
    }

    public function transform(
        Command $command,
        callable $transformChildren,
        callable $normalizeText,
        PrintSettings $printSettings
    ): string {
        return match (true) {
            $command instanceof Logo => $this->logo($command),
            $command instanceof ImageFile => $this->imageFile($command),
            default => throw new \LogicException('Unsupported barcode command ' . $command::class),
        };
    }

    public function logo(Logo $command): string
    {
        $logoNumber = (int) $command->number;
        if ($logoNumber < 1 || $logoNumber > 255) {
            throw new \InvalidArgumentException("Logo number must be 1–255, got {$logoNumber}");
        }

        // ESC FS p n m (1B 1C 70 n m)
        $nByte = chr($logoNumber);
        $mByte = chr(0); // normal size
        return "\x1B\x1C\x70" . $nByte . $mByte;
    }

    private function imageFile(ImageFile $command): string
    {
        throw new \LogicException(
            "😭 I am unable to get the image printing to work.\n" .
            "Please register images as logos using the printer configuration tool,\n" .
            "then use LauLaman\ReceiptPrinter\the Logo command to print them.\n" .
            "You should be able to find the 'Star Quick Setup Utility' in the AppStore.\n"
        );
    }
}
