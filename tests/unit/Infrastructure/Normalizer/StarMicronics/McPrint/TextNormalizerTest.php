<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\Infrastructure\Normalizer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Settings\CodePageSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Enum\CodePage;
use LauLaman\ReceiptPrinter\Infrastructure\Normalizer\StarMicronics\McPrintTextNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextNormalizerTest extends TestCase
{
    private McPrintTextNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new McPrintTextNormalizer();
    }

    #[Test]
    public function itReplacesEuroSignForCp858(): void
    {
        $settings = new PrintSettings();
        $settings->add(new CodePageSetting(CodePage::CP_858));

        $result = $this->normalizer->normalize('Price: €10', $settings);

        $this->assertSame("Price: \xD510", $result);
    }

    #[Test]
    public function itReplacesEuroSignForCp1252(): void
    {
        $settings = new PrintSettings();
        $settings->add(new CodePageSetting(CodePage::CP_1252));

        $result = $this->normalizer->normalize('Price: €10', $settings);

        $this->assertSame("Price: \x8010", $result);
    }

    #[Test]
    public function itFallsBackToLetterEWhenNoCodePageSettingIsPresent(): void
    {
        $settings = new PrintSettings();

        $result = $this->normalizer->normalize('Price: €10', $settings);

        $this->assertSame('Price: E10', $result);
    }

    #[Test]
    public function itFallsBackToLetterEForUnsupportedCodePages(): void
    {
        $settings = new PrintSettings();
        $settings->add(new CodePageSetting(CodePage::CP_437));

        $result = $this->normalizer->normalize('Price: €10', $settings);

        $this->assertSame('Price: E10', $result);
    }
}
