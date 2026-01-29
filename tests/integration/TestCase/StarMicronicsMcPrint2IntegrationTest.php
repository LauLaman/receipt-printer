<?php

declare(strict_types=1);

namespace Tests\Integration\LauLaman\ReceiptPrinter\TestCase;

use LauLaman\ReceiptPrinter\Application\Printer;
use LauLaman\ReceiptPrinter\Application\ReceiptBuilder;
use LauLaman\ReceiptPrinter\Domain\Receipt;
use LauLaman\ReceiptPrinter\Domain\Settings\CodePageSetting;
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;
use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;
use LauLaman\ReceiptPrinter\Domain\Enum\FontType;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Infrastructure\Normalizer\StarMicronics\McPrintTextNormalizer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrintTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Integration\LauLaman\ReceiptPrinter\Infrastructure\Transport\InMemoryTransport;

class StarMicronicsMcPrint2IntegrationTest extends TestCase
{
    #[Test]
    #[DataProvider('canSentBytesToPrinterProvider')]
    public function itSendsReceiptBytesToPrinter($printer, $paper, Receipt $receipt, $expected): void
    {
        $inMemoryTransport = new InMemoryTransport();

        $printer = new Printer(
            $printer,
            $paper,
            McPrintTransformer::create(new McPrintTextNormalizer()),
            $inMemoryTransport
        );

        $printer->print($receipt);

        $this->assertSame($expected, bin2hex($inMemoryTransport->getData()));
    }

    public static function canSentBytesToPrinterProvider(): array
    {
        $wifiReceipt = self::getWifiReceipt();
        $storeReceipt = self::getStoreReceipt();

        return [
            //-- Wi-Fi receipt
            'mC-Print 2 Wi-Fi Receipt on Lage paper' => [PrinterModel::STAR_MC_PRINT2, PaperWidth::LARGE,  $wifiReceipt, '1b1d61011b451b6902021b6802576946690a1b6900001b68001b461b6901011b6801596f7572576966690a1b6900001b68002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6901011b68011b3431323334352d36373839300a1b351b6900001b68001b1e46011b2d0156616c696420756e74696c20323032362d30312d32392030333a32323a33390a1b2d001b1e46002e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e0a1b1d795330021b1d795331011b1d795332051b1d794431001600574946493a543a6e6f706173733b533a535349443b3b1b1d79500a1b1d61000a0a0a0a1b6403'],
            'mC-Print 2 Wi-Fi Receipt on Small paper' => [PrinterModel::STAR_MC_PRINT2, PaperWidth::SMALL, $wifiReceipt, '1b1d61011b451b6902021b6802576946690a1b6900001b68001b461b6901011b6801596f7572576966690a1b6900001b68002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6901011b68011b3431323334352d36373839300a1b351b6900001b68001b1e46011b2d0156616c696420756e74696c20323032362d30312d32392030333a32323a33390a1b2d001b1e46002e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e0a1b1d795330021b1d795331011b1d795332051b1d794431001600574946493a543a6e6f706173733b533a535349443b3b1b1d79500a1b1d61000a0a0a0a1b6403'],
            'mC-Print 3 Wi-Fi Receipt on Large paper' => [PrinterModel::STAR_MC_PRINT3, PaperWidth::LARGE, $wifiReceipt, '1b1d61011b451b6902021b6802576946690a1b6900001b68001b461b6901011b6801596f7572576966690a1b6900001b68002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6901011b68011b3431323334352d36373839300a1b351b6900001b68001b1e46011b2d0156616c696420756e74696c20323032362d30312d32392030333a32323a33390a1b2d001b1e46002e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e0a1b1d795330021b1d795331011b1d795332051b1d794431001600574946493a543a6e6f706173733b533a535349443b3b1b1d79500a1b1d61000a0a0a0a1b6403'],
            'mC-Print 3 Wi-Fi Receipt on Small paper' => [PrinterModel::STAR_MC_PRINT3, PaperWidth::SMALL, $wifiReceipt, '1b1d61011b451b6902021b6802576946690a1b6900001b68001b461b6901011b6801596f7572576966690a1b6900001b68002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6901011b68011b3431323334352d36373839300a1b351b6900001b68001b1e46011b2d0156616c696420756e74696c20323032362d30312d32392030333a32323a33390a1b2d001b1e46002e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e0a1b1d795330021b1d795331011b1d795332051b1d794431001600574946493a543a6e6f706173733b533a535349443b3b1b1d79500a1b1d61000a0a0a0a1b6403'],
            //-- Store receipt
            'mC-Print 2 Store Receipt on Lage paper' => [PrinterModel::STAR_MC_PRINT2, PaperWidth::LARGE,  $storeReceipt, '1b1d74041b1d61011b1c7002001b451b6901011b68014d592053544f52450a1b6900001b68001b46313233204d61696e205374726565740a436974792c2053746174652031323334350a54656c3a202835353529203132332d343536370a0a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6902021b68031b3431303839370a1b351b6900001b68001b1d61002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a3120782043617070756363696e6f2020202020202020202020202020202020202020202020202020202020d5342e35300a1b343120782050697a202020202020202020202020202020202020202020202020202020202020202020d531332e35300a20202020322078204465706f736974202020202020202020202020202020202020202020202020202020201b34d5302e33301b350a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b45544f54414c20202020202020202020202020202020202020202020202020202020202020202020202020d534332e33300a1b460a1b1d61015468616e6b20796f7520666f7220796f7572207075726368617365210a0a0a1b62060201503132333435361e0a1b1d61000a0a0a0a1b6402'],
            'mC-Print 2 Store Receipt on Small paper' => [PrinterModel::STAR_MC_PRINT2, PaperWidth::SMALL, $storeReceipt, '1b1d74041b1d61011b1c7002001b451b6901011b68014d592053544f52450a1b6900001b68001b46313233204d61696e205374726565740a436974792c2053746174652031323334350a54656c3a202835353529203132332d343536370a0a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6902021b68031b3431303839370a1b351b6900001b68001b1d61002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a3120782043617070756363696e6f20202020202020202020202020d5342e35300a1b343120782050697a2020202020202020202020202020202020d531332e35300a20202020322078204465706f7369742020202020202020202020201b34d5302e33301b350a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b45544f54414c202020202020202020202020202020202020202020d534332e33300a1b460a1b1d61015468616e6b20796f7520666f7220796f7572207075726368617365210a0a0a1b62060201503132333435361e0a1b1d61000a0a0a0a1b6402'],
            'mC-Print 3 Store Receipt on Large paper' => [PrinterModel::STAR_MC_PRINT3, PaperWidth::LARGE, $storeReceipt, '1b1d74041b1d61011b1c7002001b451b6901011b68014d592053544f52450a1b6900001b68001b46313233204d61696e205374726565740a436974792c2053746174652031323334350a54656c3a202835353529203132332d343536370a0a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6902021b68031b3431303839370a1b351b6900001b68001b1d61002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a3120782043617070756363696e6f2020202020202020202020202020202020202020202020202020202020d5342e35300a1b343120782050697a202020202020202020202020202020202020202020202020202020202020202020d531332e35300a20202020322078204465706f736974202020202020202020202020202020202020202020202020202020201b34d5302e33301b350a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b45544f54414c20202020202020202020202020202020202020202020202020202020202020202020202020d534332e33300a1b460a1b1d61015468616e6b20796f7520666f7220796f7572207075726368617365210a0a0a1b62060201503132333435361e0a1b1d61000a0a0a0a1b6402'],
            'mC-Print 3 Store Receipt on Small paper' => [PrinterModel::STAR_MC_PRINT3, PaperWidth::SMALL, $storeReceipt, '1b1d74041b1d61011b1c7002001b451b6901011b68014d592053544f52450a1b6900001b68001b46313233204d61696e205374726565740a436974792c2053746174652031323334350a54656c3a202835353529203132332d343536370a0a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b6902021b68031b3431303839370a1b351b6900001b68001b1d61002d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a3120782043617070756363696e6f2020202020202020202020202020d5342e35300a1b343120782050697a202020202020202020202020202020202020d531332e35300a20202020322078204465706f736974202020202020202020202020201b34d5302e33301b350a2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d2d0a1b45544f54414c20202020202020202020202020202020202020202020d534332e33300a1b460a1b1d61015468616e6b20796f7520666f7220796f7572207075726368617365210a0a0a1b62060201503132333435361e0a1b1d61000a0a0a0a1b6402'],
        ];
    }

    private static function getWifiReceipt(): Receipt
    {
        return ReceiptBuilder::create()
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
                    ->underline("Valid until 2026-01-29 03:22:39")
                ->end()
                ->separator('.')
                ->qrCode('WIFI:T:nopass;S:SSID;;')
            ->end()
            ->cut(partial: true)
            ->build();
    }

    private static function getStoreReceipt(): Receipt
    {
        return ReceiptBuilder::create(new CodePageSetting())
            ->align(Alignment::CENTER)
                ->logo(2)
                ->bold()
                    ->magnify(2, text: "MY STORE")
                ->end()
                ->text("123 Main Street")
                ->text("City, State 12345")
                ->text("Tel: (555) 123-4567")
                ->feed()
                ->separator()
                ->magnify(3, 4)
                    ->invert("10897")
                ->end()
            ->end()
            ->separator()
            ->column("1 x Cappuccino", "€4.50")
            ->column(fn($left) => $left->invert("1 x Pizza"), "€13.50")
            ->column("    2 x Deposit",  fn($right) => $right->invert("€0.30"))
            ->separator()
            ->bold()
                ->column("TOTAL", "€43.30")
            ->end()
            ->feed()
            ->align(Alignment::CENTER)
                ->text("Thank you for your purchase!")
                ->feed(2)
                ->barcode("123456", BarcodeType::CODE128)
            ->end()
            ->cut()
            ->build();
    }
}