<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Paper\Cut;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\{
    Align, Bold, Column, Font, Magnify, Margin, Text, Underline, Upperline, UpsideDown, Invert
};
use LauLaman\ReceiptPrinter\Domain\Enum\{Alignment, FontType, PaperWidth};
use LauLaman\ReceiptPrinter\Domain\Settings\{PaperWidthSetting, PrintSettings};
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\LayoutTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LayoutTransformerTest extends TestCase
{
    private LayoutTransformer $transformer;
    private PrintSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new LayoutTransformer();
        $paperSetting = new PaperWidthSetting(PaperWidth::SMALL, charsPerLine: 32);
        $this->settings = new PrintSettings();
        $this->settings->add($paperSetting);
    }

    private function recursiveTransform(): callable
    {
        $recursiveTransform = null;
        $recursiveTransform = function ($cmd) use (&$recursiveTransform) {
            if ($cmd instanceof Text) return $cmd->value;
            if (method_exists($cmd, 'children')) {
                $output = '';
                foreach ($cmd->children() as $child) $output .= $recursiveTransform($child);
                return $output;
            }
            return '';
        };

        return $recursiveTransform;
    }



    private function normalize(): callable
    {
        return fn($val) => $val;
    }

    #[Test]
    public function itTransformsBoldCommand(): void
    {
        $bold = new Bold(new Text('Hi'));
        $result = $this->transformer->transform($bold, $this->recursiveTransform(), $this->normalize(), $this->settings);

        $this->assertSame("\x1B\x45Hi\x1B\x46", $result);
    }

    #[Test]
    public function itTransformsUnderlineCommand(): void
    {
        $uline = new Underline(new Text('Under'));

        $result = $this->transformer->transform($uline, $this->recursiveTransform(), $this->normalize(), $this->settings);

        $this->assertSame("\x1B\x2D\x01Under\x1B\x2D\x00", $result);
    }

    #[Test]
    public function itTransformsUpperlineCommand(): void
    {
        $uline = new Upperline(new Text('Upper'));

        $result = $this->transformer->transform($uline, $this->recursiveTransform(), $this->normalize(), $this->settings);

        $this->assertSame("\x1B\x5F\x01Upper\x1B\x5F\x00", $result);
    }

    #[Test]
    public function itTransformsUpsideDownCommand(): void
    {
        $ud = new UpsideDown(new Text('Flip'));

        $result = $this->transformer->transform($ud, $this->recursiveTransform(), $this->normalize(), $this->settings);

        $this->assertSame("\x0FFlip\x12", $result);
    }

    #[Test]
    public function itTransformsInvertCommand(): void
    {
        $inv = new Invert(new Text('Invert'));

        $result = $this->transformer->transform($inv, $this->recursiveTransform(), $this->normalize(), $this->settings);

        $this->assertSame("\x1B\x34Invert\x1B\x35", $result);
    }

    #[Test]
    #[DataProvider('alignmentProvider')]
    public function itAlignsTextCorrectly(Alignment $alignment, string $text, string $expectedOutput): void
    {
        $align = new Align($alignment, new Text($text));

        $this->assertSame(
            $expectedOutput,
            $this->transformer->transform($align, $this->recursiveTransform(), $this->normalize(), $this->settings)
        );
    }

    public static function alignmentProvider(): array
    {
        return [
            [Alignment::LEFT, 'Left', "\x1B\x1D\x61\x00Left\x1B\x1D\x61\x00"],
            [Alignment::CENTER, 'Center', "\x1B\x1D\x61\x01Center\x1B\x1D\x61\x00"],
            [Alignment::RIGHT, 'Right', "\x1B\x1D\x61\x02Right\x1B\x1D\x61\x00"],
        ];
    }

    #[Test]
    public function itTransformsMarginCommand(): void
    {
        $margin = new Margin(5, 3, new Text('Margin'));
        $paperWidthChars = $this->settings->get(PaperWidthSetting::class)->charsPerLine;
        $expectedPrintRegion = $paperWidthChars - 5 - 3;
        $expected = "\x1B\x6C\x05\x1B\x51" . chr($expectedPrintRegion) . "Margin\x1B\x6C\x00\x1B\x51" . chr($paperWidthChars);

        $this->assertSame(
            $expected,
            $this->transformer->transform($margin, $this->recursiveTransform(), $this->normalize(), $this->settings)
        );
    }

    #[Test]
    public function itTransformsMagnifyCommand(): void
    {
        $mag = new Magnify(2, 3, new Text('Big'));

        $result = $this->transformer->transform($mag, $this->recursiveTransform(), $this->normalize(), $this->settings);

        $this->assertSame("\x1B\x69\x01\x01\x1B\x68\x02Big\x1B\x69\x00\x00\x1B\x68\x00", $result);
    }

    #[Test]
    #[DataProvider('fontProvider')]
    public function itTransformsFontCommand(FontType $fontType, string $text, string $expectedOutput): void
    {
        $font = new Font($fontType, new Text($text));

        $this->assertSame(
            $expectedOutput,
            $this->transformer->transform($font, $this->recursiveTransform(), $this->normalize(), $this->settings)
        );
    }

    public static function fontProvider(): array
    {
        return [
            [FontType::A, 'FontA', "\x1B\x1E\x46\x00FontA\x1B\x1E\x46\x00"],
            [FontType::B, 'FontB', "\x1B\x1E\x46\x01FontB\x1B\x1E\x46\x00"],
            [FontType::C, 'FontC', "\x1B\x1E\x46\x02FontC\x1B\x1E\x46\x00"],
        ];
    }

    #[Test]
    public function itTransformsColumnCommand(): void
    {
        $left = [new Text('Left')];
        $right = [new Text('Right')];
        $column = new Column($left, $right, ' ');

        $result = $this->transformer->transform($column, fn($c) => $c instanceof Text ? $c->value : '', fn($val) => $val, $this->settings);

        $this->assertSame("Left                       Right\n", $result);
    }

    public function testColumnWithLongTextOnLeft(): void
    {
        $left = [new Text('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.')];
        $right = [new Text('$12.99')];

        $column = new Column($left, $right, ' ');

        $output = $this->transformer->transform(
            $column,
            fn($c) => $c instanceof Text ? $c->value : '',
            fn($val) => $val,
            $this->settings
        );

        $this->assertSame('4c6f72656d20697073756d20646f6c6f722073697420616d20202431322e39390a2020742c20636f6e73656374657475722061646970697363696e0a202020656c69742c2073656420646f20656975736d6f642074650a2020706f7220696e6369646964756e74207574206c61626f72650a2020657420646f6c6f7265206d61676e6120616c697175612e0a20204c6f72656d20697073756d20646f6c6f722073697420616d65742c20636f6e73656374657475722061646970697363696e6720656c69742c2073656420646f20656975736d6f642074656d706f7220696e6369646964756e74207574206c61626f726520657420646f6c6f7265206d61676e6120616c697175612e0a', bin2hex($output));
    }

}
