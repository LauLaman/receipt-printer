<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Settings\CodePageSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\CodePage;

class SettingsTransformer implements CommandTransformer
{
    public function supports(Command $command): bool
    {
        return $command instanceof PrintSetting;
    }

    public function transform(
        Command $command,
        callable $transformChildren,
        callable $normalizeText,
        PrintSettings $printSettings
    ): string {
        return match (true) {
            $command instanceof CodePageSetting => $this->setCodePage($command),
            default => throw new \LogicException('Unsupported print setting command ' . $command::class),
        };
    }

    private function setCodePage(CodePageSetting $command): string
    {
        $type = match($command->codePage) {
            CodePage::DEFAULT => 0,
            CodePage::CP_437 => 1,
            CodePage::KATAKANA => 2,
            CodePage::CP_858 => 4,
            CodePage::CP_852 => 5,
            CodePage::CP_860 => 6,
            CodePage::CP_861 => 7,
            CodePage::CP_863 => 8,
            CodePage::CP_865 => 9,
            CodePage::CP_866 => 10,
            CodePage::CP_855 => 11,
            CodePage::CP_857 => 12,
            CodePage::CP_862 => 13,
            CodePage::CP_864 => 14,
            CodePage::CP_737 => 15,
            CodePage::CP_851 => 16,
            CodePage::CP_869 => 17,
            CodePage::CP_928 => 18,
            CodePage::CP_772 => 19,
            CodePage::CP_774 => 20,
            CodePage::CP_874 => 21,
            CodePage::CP_1252 => 32,
            CodePage::CP_1250 => 33,
            CodePage::CP_1251 => 34,
            CodePage::CP_3840 => 64,
            CodePage::CP_3841 => 65,
            CodePage::CP_3843 => 66,
            CodePage::CP_3844 => 67,
            CodePage::CP_3845 => 68,
            CodePage::CP_3846 => 69,
            CodePage::CP_3847 => 70,
            CodePage::CP_3848 => 71,
            CodePage::CP_1001 => 72,
            CodePage::CP_2001 => 73,
            CodePage::CP_3001 => 74,
            CodePage::CP_3002 => 75,
            CodePage::CP_3011 => 76,
            CodePage::CP_3012 => 77,
            CodePage::CP_3021 => 78,
            CodePage::CP_3041 => 79,
            CodePage::THAI_CC_42 => 96,
            CodePage::THAI_CC_11 => 97,
            CodePage::THAI_CC_13 => 98,
            CodePage::THAI_CC_18 => 102,
            CodePage::USER_DEFINED => 255,
        };

        return "\x1B\x1D\x74" . chr($type);
    }
}
