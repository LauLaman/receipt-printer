<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Application\ReceiptBuilder;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Barcode;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\GS1DataBarExpanded;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Pdf417;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\QRCode;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\ImageFile;
use LauLaman\ReceiptPrinter\Domain\Command\Graphics\Logo;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Align;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Bold;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Column;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Font;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Invert;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Magnify;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Separator;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Text;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Underline;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Upperline;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\UpsideDown;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\Cut;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\Feed;
use LauLaman\ReceiptPrinter\Domain\Command\Paper\OpenDrawer;
use LauLaman\ReceiptPrinter\Domain\Receipt;
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;
use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;
use LauLaman\ReceiptPrinter\Domain\Enum\FontType;
use LauLaman\ReceiptPrinter\Domain\Enum\QRCodeErrorCorrection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReceiptBuilderTest extends TestCase
{
    #[Test]
    public function itCanAddTextAndBoldCommands(): void
    {
        $receipt = ReceiptBuilder::create()
            ->text('Hello')
            ->bold('Bold Text')
            ->build();

        $commands = $receipt->getCommands();
        $this->assertCount(2, $commands);
        $this->assertInstanceOf(Text::class, $commands[0]);
        $this->assertInstanceOf(Bold::class, $commands[1]);
    }

    #[Test]
    public function itCanUseAlignAndFontCommands(): void
    {
        $receipt = ReceiptBuilder::create()
            ->align(Alignment::CENTER)->end()
            ->font(FontType::A, 'Font A Text')
            ->build();

        $commands = $receipt->getCommands();
        $this->assertInstanceOf(Align::class, $commands[0]);
        $this->assertInstanceOf(Font::class, $commands[1]);
    }

    #[Test]
    public function itCanUseInvertMagnifyUnderlineUpperlineAndUpsideDown(): void
    {
        $receipt = ReceiptBuilder::create()
            ->invert('Invert Text')
            ->magnify(2, 3, 'Magnified')
            ->underline('Underline')
            ->upperline('Upperline')
            ->upsideDown('UpsideDown')
            ->build();

        $commands = $receipt->getCommands();
        $this->assertInstanceOf(Invert::class, $commands[0]);
        $this->assertInstanceOf(Magnify::class, $commands[1]);
        $this->assertInstanceOf(Underline::class, $commands[2]);
        $this->assertInstanceOf(Upperline::class, $commands[3]);
        $this->assertInstanceOf(UpsideDown::class, $commands[4]);
    }

    #[Test]
    public function itCanUseColumnAndSeparatorCommands(): void
    {
        $receipt = ReceiptBuilder::create()
            ->column('Left', 'Right')
            ->separator('-')
            ->build();

        $commands = $receipt->getCommands();
        $this->assertInstanceOf(Column::class, $commands[0]);
        $this->assertInstanceOf(Separator::class, $commands[1]);
    }

    #[Test]
    public function itCanUsePaperCommands(): void
    {
        $receipt = ReceiptBuilder::create()
            ->cut()
            ->feed(2)
            ->openDrawer()
            ->build();

        $commands = $receipt->getCommands();
        $this->assertInstanceOf(Cut::class, $commands[0]);
        $this->assertInstanceOf(Feed::class, $commands[1]);
        $this->assertInstanceOf(OpenDrawer::class, $commands[2]);
    }

    #[Test]
    public function itCanAddAllBarcodeCommands(): void
    {
        $receipt = ReceiptBuilder::create()
            ->barcode('123', BarcodeType::CODE128)
            ->gs1DataBarExpanded('456')
            ->pdf417('789')
            ->qrCode('ABC', 5, QRCodeErrorCorrection::MEDIUM)
            ->build();

        $commands = $receipt->getCommands();
        $this->assertInstanceOf(Barcode::class, $commands[0]);
        $this->assertInstanceOf(GS1DataBarExpanded::class, $commands[1]);
        $this->assertInstanceOf(Pdf417::class, $commands[2]);
        $this->assertInstanceOf(QRCode::class, $commands[3]);
    }

    #[Test]
    public function itCanAddGraphicsCommands(): void
    {
        $receipt = ReceiptBuilder::create()
            ->logo(1, 2)
            ->imageFile('/path/to/file', 100, 50)
            ->build();

        $commands = $receipt->getCommands();
        $this->assertInstanceOf(Logo::class, $commands[0]);
        $this->assertInstanceOf(ImageFile::class, $commands[1]);
    }

    #[Test]
    public function itCanBuildNestedCommandsWithOpenAndEnd(): void
    {
        $receipt = ReceiptBuilder::create()
            ->bold()
            ->text('Nested Bold')
            ->end()
            ->align(Alignment::RIGHT)
            ->text('Nested Align')
            ->end()
            ->build();

        $commands = $receipt->getCommands();
        $this->assertCount(2, $commands);
        $this->assertInstanceOf(Bold::class, $commands[0]);
        $this->assertInstanceOf(Align::class, $commands[1]);


        $boldChildren = $commands[0]->children();
        $this->assertCount(1, $boldChildren);
        $this->assertInstanceOf(Text::class, $boldChildren[0]);

        $alignChildren = $commands[1]->children();
        $this->assertCount(1, $alignChildren);
        $this->assertInstanceOf(Text::class, $alignChildren[0]);
    }

    #[Test]
    public function itThrowsExceptionWhenEndingWithoutOpenBlock(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No open block to end.');

        ReceiptBuilder::create()->end();
    }

    #[Test]
    public function itThrowsExceptionForUnclosedScopesOnBuild(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unclosed receipt scopes.');

        ReceiptBuilder::create()
            ->bold()
            ->build(); // forgot to end the bold block
    }
}
