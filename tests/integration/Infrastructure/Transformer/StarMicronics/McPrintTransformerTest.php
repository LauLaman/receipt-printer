<?php

declare(strict_types=1);

namespace Tests\Integration\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics;

use LauLaman\ReceiptPrinter\Application\ReceiptBuilder;
use LauLaman\ReceiptPrinter\Domain\Settings\CodePageSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PaperWidthSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrinterModelSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;
use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;
use LauLaman\ReceiptPrinter\Domain\Enum\FontType;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Infrastructure\Normalizer\StarMicronics\McPrintTextNormalizer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrintTransformer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class McPrintTransformerTest extends TestCase
{
    #[Test]
    public function itBuildsWifiCouponAndTransformsIt(): void
    {
        $model = PrinterModel::STAR_MC_PRINT2;
        $paper = PaperWidth::SMALL;

        $builder = ReceiptBuilder::create()
            ->align(Alignment::CENTER)
                ->bold()
                    ->magnify(3)
                        ->text("WiFi")
                    ->end()
                ->end()
                ->magnify(2, text: "YourWifi")
                ->separator()
                ->magnify(2)
                    ->invert("12345-67890")
                ->end()
                ->font(FontType::B)
                    ->underline("Valid until 2026-01-29 02:41:38")
                ->end()
                ->separator('.')
                ->qrCode('WIFI:T:nopass;S:SSID;;')
            ->end()
            ->cut(partial: true);

        $receipt = $builder->build();

        $printSettings = new PrintSettings();
        $printSettings->add(new PrinterModelSetting($model));
        $printSettings->add(new PaperWidthSetting($paper, $model->getCharsPerLine($paper)));
        $printSettings->add(...$receipt->getSettings());

        $transformer = McPrintTransformer::create(new McPrintTextNormalizer());
        $bytes = $transformer->transform($printSettings, ...$receipt->getCommands());

        $this->assertSame(
            '1b1d61011b451b6902021b6802576946690a1b6900001b68001b461b6901011b6801596f7572576966690a1b6900001b68002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6901011b68011b3431323334352d36373839300a1b351b6900001b68001b1e46011b2d0156616c696420756e74696c20323032362d30312d32392030323a34313a33380a1b2d001b1e46002e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e0a1b1d795330021b1d795331011b1d795332051b1d794431001600574946493a543a6e6f706173733b533a535349443b3b1b1d79500a1b1d61000a0a0a0a1b6403',
            bin2hex($bytes)
        );
    }

    #[Test]
    public function itBuildsStoreReceiptAndTransformsIt(): void
    {
        $model = PrinterModel::STAR_MC_PRINT2;
        $paper = PaperWidth::SMALL;

        $builder = ReceiptBuilder::create(new CodePageSetting())
            ->align(Alignment::CENTER)
                ->logo(2)
                ->bold()
                    ->magnify(2, text: "MY STORE")
                ->end()
                ->upsideDown()
                    ->text("123 Main Street")
                ->end()
                ->underline()
                    ->text("City, State 12345")
                ->end()
                ->upperline()
                    ->text("Tel: (555) 123-4567")
                ->end()
                ->feed()
                ->separator()
                ->magnify(3, 4)
                    ->invert("10897")
                ->end()
            ->end()
            ->separator()
            ->column("1 x Cappuccino", "€4.50")
            ->column(fn($left) => $left->invert()->text("1 x Pizza")->end(), "€13.50")
            ->column("    2 x Deposit",  fn($right) => $right->invert("€0.30"))
            ->separator()
            ->bold()
                ->column("TOTAL", "€43.30")
            ->end()
            ->feed()
            ->align(Alignment::CENTER)
                ->underline("Thank you for your purchase!")
                ->upperline("Please come again.")
                ->upsideDown("Have a nice day!")
                ->feed(2)
                ->barcode("123456", BarcodeType::CODE128)
            ->end()
            ->cut();

        $receipt = $builder->build();

        $printSettings = new PrintSettings();
        $printSettings->add(new PrinterModelSetting($model));
        $printSettings->add(new PaperWidthSetting($paper, $model->getCharsPerLine($paper)));
        $printSettings->add(...$receipt->getSettings());

        $transformer = McPrintTransformer::create(new McPrintTextNormalizer());
        $bytes = $transformer->transform($printSettings, ...$receipt->getCommands());

        $this->assertSame(
            '1b1d74041b1d61011b1c7002001b451b6901011b68014d592053544f52450a1b6900001b68001b460f313233204d61696e205374726565740a121b2d01436974792c2053746174652031323334350a1b2d001b5f0154656c3a202835353529203132332d343536370a1b5f000a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6902021b68031b3431303839370a1b351b6900001b68001b1d61002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a3120782043617070756363696e6f20202020202020202020202020d5342e35300a1b343120782050697a2020202020202020202020202020202020d531332e35300a20202020322078204465706f7369742020202020202020202020201b34d5302e33301b350a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b45544f54414c202020202020202020202020202020202020202020d534332e33300a1b460a1b1d61011b2d015468616e6b20796f7520666f7220796f7572207075726368617365210a1b2d001b5f01506c6561736520636f6d6520616761696e2e0a1b5f000f486176652061206e69636520646179210a120a0a1b62060201503132333435361e0a1b1d61000a0a0a0a1b6402',
            bin2hex($bytes)
        );
    }
    
}