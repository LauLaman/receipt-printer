<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain;

use LauLaman\ReceiptPrinter\Domain\Enum\FontType;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModelInterface;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\Dpi;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\Margin;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrintSetting\PrintSetting;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\SupportedFont;

final class PrinterSettings
{
    private ?PrintSettings $printSettings = null;

    /** @param array<SupportedFont> $supportedFonts */

    public function __construct(
        public readonly string $manufacturer,
        public readonly PrinterModelInterface $model,
        public readonly Dpi $dpi,
        public readonly PaperWidth $paperWidth,
        public readonly Margin $margin,
        public readonly array $supportedFonts
    ) {
    }

    public function setPrintSettings(PrintSettings $printSettings): void
    {
        $this->printSettings = $printSettings;
    }

    public function getPrintSetting(string $printSetting): ?PrintSetting
    {
        return $this->printSettings?->get($printSetting);
    }

    public function getPrintSettings(): ?PrintSettings
    {
        return $this->printSettings;
    }

    public function getSupportedFont(FontType $font): SupportedFont
    {
        foreach ($this->supportedFonts as $supportedFont) {
            if ($supportedFont->font === $font) {
                return $supportedFont;
            }
        }

        throw new \InvalidArgumentException(sprintf("Font %s not supported.", $font->name));
    }
}