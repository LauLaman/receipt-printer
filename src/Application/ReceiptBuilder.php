<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Barcode;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\GS1DataBarExpanded;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Pdf417;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\QRCode;
use LauLaman\ReceiptPrinter\Domain\Command\Command;
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
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSetting;
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;
use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;
use LauLaman\ReceiptPrinter\Domain\Enum\FontType;
use LauLaman\ReceiptPrinter\Domain\Enum\QRCodeErrorCorrection;

final class ReceiptBuilder
{
    /**
     * @var array<PrintSetting>
     */
    private array $printSettings = [];

    /** @var Command */
    private array $stack = [[]];
    private array $currentOpen = [];

    public static function create(PrintSetting ...$settings): self
    {
        $self = new self();
        $self->printSettings = $settings;


        return $self;
    }

    private function &current(): array
    {
        return $this->stack[array_key_last($this->stack)];
    }

    public function open(string $class, array $args = []): self
    {
        $this->currentOpen[] = [$class, $args];
        $this->stack[] = [];
        return $this;
    }

    /**
     * Ends the current block
     */
    public function end(): self
    {
        if (count($this->stack) < 2) {
            throw new \LogicException('No open block to end.');
        }

        $children = array_pop($this->stack);
        [$class, $args] = array_pop($this->currentOpen);
        $args = array_merge($args, $children);
        $this->current()[] = new $class(...$args);

        return $this;
    }

    // --------------------
    // Style commands
    // --------------------

    /**
     * Sets the text alignment
     */
    public function align(Alignment $mode): self
    {
        return $this->open(Align::class, [$mode]);
    }

    /**
     * Make text bold
     */
    public function bold(?string $text = null): self
    {
        if ($text === null) {
            return $this->open(Bold::class);
        }
        $this->current()[] = new Bold(new Text($text));
        return $this;
    }

    /**
     * Sets the font type
     */
    public function font(FontType $font, ?string $text = null): self
    {
        if ($text === null) {
            return $this->open(Font::class, [$font]);
        }
        $this->current()[] = new Font($font, new Text($text));
        return $this;
    }

    /**
     * Inverts the colors of the text
     */
    public function invert(?string $text = null): self
    {
        if ($text === null) {
            return $this->open(Invert::class);
        }
        $this->current()[] = new Invert(new Text($text));
        return $this;
    }

    /**
     * Sets the text size
     */
    public function magnify(int $width, ?int $height = null, ?string $text = null): self
    {
        if ($text === null) {
            return $this->open(Magnify::class, [$width, $height]);
        }
        $this->current()[] = new Magnify($width, $height, new Text($text));
        return $this;
    }

    // --------------------
    // Layout commands
    // --------------------

    /**
     * Prints a column of text (left and right)
     */
    public function column(string|callable $left, string|callable $right, ?string $spacer = ' '): self
    {
        if (is_string($left)) {
            $left = [new Text($left)];
        } else {
            $leftBuilder = self::create();
            $left($leftBuilder);
            $left = $leftBuilder->build()->getCommands();
        }

        if (is_string($right)) {
            $right = [new Text($right)];
        } else {
            $rightBuilder = self::create();
            $right($rightBuilder);
            $right = $rightBuilder->build()->getCommands();
        }

        $this->current()[] = new Column($left, $right, $spacer);
        return $this;
    }

    /**
     * Prints a separator line
     */
    public function separator(string $char = '-'): self
    {
        $this->current()[] = new Separator($char);
        return $this;
    }

    /**
     * Prints text
     */
    public function text(string $text): self
    {
        $this->current()[] = new Text($text);
        return $this;
    }

    /**
     * Underlines text
     */
    public function underline(?string $text = null): self
    {
        if ($text === null) {
            return $this->open(Underline::class);
        }
        $this->current()[] = new Underline(new Text($text));
        return $this;
    }

    /**
     * Underlines text
     */
    public function upperline(?string $text = null): self
    {
        if ($text === null) {
            return $this->open(Upperline::class);
        }
        $this->current()[] = new Upperline(new Text($text));
        return $this;
    }

    /**
     * Underlines text
     */
    public function upsideDown(?string $text = null): self
    {
        if ($text === null) {
            return $this->open(UpsideDown::class);
        }
        $this->current()[] = new UpsideDown(new Text($text));
        return $this;
    }

    // --------------------
    // Paper commands
    // --------------------

    /**
     * Cuts the paper
     */
    public function cut(?bool $feed = true, ?bool $partial = false): self
    {
        $this->current()[] = new Cut($feed, $partial);
        return $this;
    }

    /**
     * Feeds the paper
     */
    public function feed(int $lines = 1): self
    {
        $this->current()[] = new Feed($lines);
        return $this;
    }

    /**
     * Opens the cash drawer connected to the printer
     */
    public function openDrawer(): self
    {
        $this->current()[] = new OpenDrawer();
        return $this;
    }

    // --------------------
    // Barcode commands
    // --------------------

    /**
     * Prints a barcode
     */
    public function barcode(
        string $data,
        BarcodeType $type = BarcodeType::CODE39,
        int $width = 1,
        int $height = 80,
        bool $hri = true
    ): self {
        $this->current()[] = new Barcode($data, $type, $width, $height, $hri);
        return $this;
    }

    /**
     * Prints a GS1 DataBar Expanded barcode
     */
    public function gs1DataBarExpanded(string $data): self
    {
        $this->current()[] = new GS1DataBarExpanded($data);
        return $this;
    }

    /**
     * Prints a PDF417 barcode
     */
    public function pdf417(
        string $data,
        ?int $sizeMode = 0,
        ?int $verticalWeight = 2,
        ?int $horizontalWeight = 3,
        ?int $ecc = 3,
        ?int $moduleX = 3,
        ?int $aspect = 3
    ): self {
        $this->current()[] = new Pdf417($data, $sizeMode, $verticalWeight, $horizontalWeight, $ecc, $moduleX, $aspect);
        return $this;
    }

    /**
     * Prints a QR code
     */
    public function qrCode(
        string $data,
        int $size = 5,
        QRCodeErrorCorrection $ec = QRCodeErrorCorrection::MEDIUM,
    ): self {
        $this->current()[] = new QRCode($data, $size, $ec);
        return $this;
    }

    // --------------------
    // Graphics commands
    // --------------------

    /**
     * Print one of the logos stored on the printer
     */
    public function logo(?int $number = 1, ?int $scale = 1): self
    {
        $this->current()[] = new Logo($number, $scale);
        return $this;
    }

    /**
     * @deprecated I'm unable to get this to work. use LauLaman\ReceiptPrinter\logo() for now
     */
    public function imageFile(string $path, $with, $height, ?int $scale = 1)
    {
        $this->current()[] = new ImageFile($path, $with, $height, $scale);
        return $this;
    }

    // --------------------
    // Build
    // --------------------

    /**
     * Builds the receipt
     */
    public function build(): Receipt
    {
        if (count($this->stack) !== 1) {
            throw new \LogicException('Unclosed receipt scopes.');
        }

        return new Receipt($this->printSettings, $this->stack[0]);
    }
}