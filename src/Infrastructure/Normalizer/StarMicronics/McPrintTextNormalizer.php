<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Normalizer\StarMicronics;

use LauLaman\ReceiptPrinter\Domain\Normalizer\TextNormalizer;
use LauLaman\ReceiptPrinter\Domain\Settings\CodePageSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Enum\CodePage;

final readonly class McPrintTextNormalizer implements TextNormalizer
{
    public function normalize(string $text, PrintSettings $printSettings): string
    {
        /** @var ?CodePageSetting $codePageSetting */
        $codePageSetting = $printSettings->get(CodePageSetting::class);

        $output = match($codePageSetting?->codePage) {
            CodePage::CP_858 => str_replace('€', "\xD5", $text),
            CodePage::CP_1252 => str_replace('€', "\x80", $text),
            default => str_replace('€', 'E', $text),
        };

        return $output;
    }
}