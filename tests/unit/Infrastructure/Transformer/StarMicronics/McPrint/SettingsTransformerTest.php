<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Settings\CodePageSetting;
use LauLaman\ReceiptPrinter\Domain\Enum\CodePage;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\SettingsTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SettingsTransformerTest extends TestCase
{
    private SettingsTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new SettingsTransformer();
    }

    #[Test]
    #[DataProvider('codePageProvider')]
    public function itTransformsCodePage(CodePage $page, string $expected): void
    {
        $settings = new PrintSettings();
        $cmd = new CodePageSetting($page);

        $result = $this->transformer->transform(
            $cmd,
            fn($c) => '', // transformChildren not used
            fn($text) => $text, // normalizeText not used
            $settings
        );

        $this->assertSame($expected, $result, "Failed asserting CodePage {$page->name}");
    }

    public static function codePageProvider(): array
    {
        return [
            [CodePage::DEFAULT,       "\x1B\x1D\x74\x00"],
            [CodePage::CP_437,        "\x1B\x1D\x74\x01"],
            [CodePage::KATAKANA,      "\x1B\x1D\x74\x02"],
            [CodePage::CP_858,        "\x1B\x1D\x74\x04"],
            [CodePage::CP_852,        "\x1B\x1D\x74\x05"],
            [CodePage::CP_860,        "\x1B\x1D\x74\x06"],
            [CodePage::CP_861,        "\x1B\x1D\x74\x07"],
            [CodePage::CP_863,        "\x1B\x1D\x74\x08"],
            [CodePage::CP_865,        "\x1B\x1D\x74\x09"],
            [CodePage::CP_866,        "\x1B\x1D\x74\x0A"],
            [CodePage::CP_855,        "\x1B\x1D\x74\x0B"],
            [CodePage::CP_857,        "\x1B\x1D\x74\x0C"],
            [CodePage::CP_862,        "\x1B\x1D\x74\x0D"],
            [CodePage::CP_864,        "\x1B\x1D\x74\x0E"],
            [CodePage::CP_737,        "\x1B\x1D\x74\x0F"],
            [CodePage::CP_851,        "\x1B\x1D\x74\x10"],
            [CodePage::CP_869,        "\x1B\x1D\x74\x11"],
            [CodePage::CP_928,        "\x1B\x1D\x74\x12"],
            [CodePage::CP_772,        "\x1B\x1D\x74\x13"],
            [CodePage::CP_774,        "\x1B\x1D\x74\x14"],
            [CodePage::CP_874,        "\x1B\x1D\x74\x15"],
            [CodePage::CP_1252,       "\x1B\x1D\x74\x20"],
            [CodePage::CP_1250,       "\x1B\x1D\x74\x21"],
            [CodePage::CP_1251,       "\x1B\x1D\x74\x22"],
            [CodePage::CP_3840,       "\x1B\x1D\x74\x40"],
            [CodePage::CP_3841,       "\x1B\x1D\x74\x41"],
            [CodePage::CP_3843,       "\x1B\x1D\x74\x42"],
            [CodePage::CP_3844,       "\x1B\x1D\x74\x43"],
            [CodePage::CP_3845,       "\x1B\x1D\x74\x44"],
            [CodePage::CP_3846,       "\x1B\x1D\x74\x45"],
            [CodePage::CP_3847,       "\x1B\x1D\x74\x46"],
            [CodePage::CP_3848,       "\x1B\x1D\x74\x47"],
            [CodePage::CP_1001,       "\x1B\x1D\x74\x48"],
            [CodePage::CP_2001,       "\x1B\x1D\x74\x49"],
            [CodePage::CP_3001,       "\x1B\x1D\x74\x4A"],
            [CodePage::CP_3002,       "\x1B\x1D\x74\x4B"],
            [CodePage::CP_3011,       "\x1B\x1D\x74\x4C"],
            [CodePage::CP_3012,       "\x1B\x1D\x74\x4D"],
            [CodePage::CP_3021,       "\x1B\x1D\x74\x4E"],
            [CodePage::CP_3041,       "\x1B\x1D\x74\x4F"],
            [CodePage::THAI_CC_42,    "\x1B\x1D\x74\x60"],
            [CodePage::THAI_CC_11,    "\x1B\x1D\x74\x61"],
            [CodePage::THAI_CC_13,    "\x1B\x1D\x74\x62"],
            [CodePage::THAI_CC_18,    "\x1B\x1D\x74\x66"],
            [CodePage::USER_DEFINED,  "\x1B\x1D\x74\xFF"],
        ];
    }
}
