<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Domain\Settings;

use InvalidArgumentException;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Domain\Settings\PaperWidthSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrinterModelSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSetting;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrintSettingsTest extends TestCase
{
    #[Test]
    public function itCanAddAndRetrieveASingleSetting(): void
    {
        $settings = new PrintSettings();
        $setting = $this->createMock(PrintSetting::class);
        $className = get_class($setting);

        $settings->add($setting);

        $this->assertSame($setting, $settings->get($className));
    }

    #[Test]
    public function itReturnsNullForAnUnknownSetting(): void
    {
        $settings = new PrintSettings();
        $setting = $this->createMock(PrintSetting::class);
        $className = get_class($setting);

        $this->assertNull($settings->get($className));
    }

    #[Test]
    public function itCanAddMultipleDifferentSettings(): void
    {
        $settings = new PrintSettings();

        $settingA = new PaperWidthSetting(PaperWidth::SMALL, 33);
        $settingB = new PrinterModelSetting(PrinterModel::STAR_MC_PRINT2);

        $settings->add($settingA, $settingB);

        $this->assertSame($settingA, $settings->get(PaperWidthSetting::class));
        $this->assertSame($settingB, $settings->get(PrinterModelSetting::class));
    }

    #[Test]
    public function itReturnsAllSettings(): void
    {
        $settings = new PrintSettings();

        $settingA = new PaperWidthSetting(PaperWidth::SMALL, 33);
        $settingB = new PrinterModelSetting(PrinterModel::STAR_MC_PRINT2);

        $settings->add($settingA, $settingB);

        $this->assertSame(
            [
                PaperWidthSetting::class => $settingA,
                PrinterModelSetting::class => $settingB,
            ],
            $settings->all()
        );
    }

    #[Test]
    public function itThrowsWhenAddingTheSameSettingTwice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already set');

        $settings = new PrintSettings();
        $setting = $this->createMock(PrintSetting::class);

        $settings->add($setting);
        $settings->add($setting);
    }
}
