<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\ContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Align;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Bold;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Column;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Font;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Invert;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\LayoutCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Magnify;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Margin;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Separator;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Text;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Underline;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\Upperline;
use LauLaman\ReceiptPrinter\Domain\Command\Layout\UpsideDown;
use LauLaman\ReceiptPrinter\Domain\Settings\PaperWidthSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;
use LauLaman\ReceiptPrinter\Domain\Enum\FontType;

class LayoutTransformer implements CommandTransformer
{
    public function supports(Command $command): bool
    {
        return $command instanceof LayoutCommand;
    }

    public function transform(
        Command $command,
        callable $transformChildren,
        callable $normalizeText,
        PrintSettings $printSettings
    ): string {
        /** @var PaperWidthSetting $paperWidthSetting */
        $paperWidthSetting =  $printSettings->get(PaperWidthSetting::class);

        return match (true) {
            $command instanceof Text => $normalizeText($command->value) . "\n",
            $command instanceof Separator => str_repeat($command->char, (int)floor($paperWidthSetting->charsPerLine / $paperWidthSetting->charsPerLineMultiplier)) . "\n",
            $command instanceof Column => $this->twoColumns($command, $transformChildren, $normalizeText, $printSettings),
            $command instanceof ContainerCommand => $this->container($command, $transformChildren, $normalizeText, $printSettings),
            default => throw new \LogicException('Unsupported text command ' . $command::class),
        };
    }

    private function container(ContainerCommand $command, callable $transformChildren, callable $normalizeText, PrintSettings $printSettings): string
    {
        /** @var PaperWidthSetting $paperWidthSetting */
        $paperWidthSetting =  $printSettings->get(PaperWidthSetting::class);

        return match (true) {
            $command instanceof Bold => "\x1B\x45" . $transformChildren($command) . "\x1B\x46",
            $command instanceof Underline => "\x1B\x2D\x01" . $transformChildren($command) . "\x1B\x2D\x00",
            $command instanceof Upperline => "\x1B\x5F\x01" . $transformChildren($command) . "\x1B\x5F\x00",
            $command instanceof UpsideDown => "\x0F" . $transformChildren($command) . "\x12",
            $command instanceof Invert => "\x1B\x34" . $transformChildren($command) . "\x1B\x35",
            $command instanceof Align => $this->align($command, $transformChildren),
            $command instanceof Margin => $this->margin($command, $transformChildren, $paperWidthSetting),
            $command instanceof Magnify => $this->magnify($command, $transformChildren, $paperWidthSetting),
            $command instanceof Font => $this->font($command, $transformChildren),
            default => throw new \LogicException('Unknown container ' . $command::class),
        };
    }

    private function align(Align $align, callable $transformChildren): string
    {
        $mode = match($align->mode) {
            Alignment::LEFT => "\x1B\x1D\x61\x00",
            Alignment::CENTER => "\x1B\x1D\x61\x01",
            Alignment::RIGHT => "\x1B\x1D\x61\x02",
        };
        return $mode . $transformChildren($align) . "\x1B\x1D\x61\x00";
    }

    private function margin(Margin $margin, callable $transformChildren, PaperWidthSetting $paperWidthSetting): string
    {
        $mode = '';

        // Set left margin
        if ($margin->left !== null) {
            $leftMargin = max(0, min(255, $margin->left));
            $mode .= "\x1B\x6C" . chr($leftMargin);
        } else {
            $leftMargin = 0;
        }

        // Set print region (ESC Q sets the WIDTH of print region from left edge)
        if ($margin->right !== null) {
            // Calculate print region: total width - left margin - right margin
            $rightMargin = max(0, min(255, $margin->right));
            $printRegion = $paperWidthSetting->charsPerLine - $leftMargin - $rightMargin;

            // Ensure minimum 36mm print region (roughly 18 chars on 57mm, 24 chars on 80mm)
            $minChars = $paperWidthSetting->charsPerLine == 32 ? 18 : 24;
            $printRegion = max($minChars, $printRegion);

            $mode .= "\x1B\x51" . chr($printRegion);
        }

        // Apply margins, render content, then reset
        $content = $mode . $transformChildren($margin);

        // Reset margins
        $reset = "\x1B\x6C\x00"; // Reset left margin to 0
        $reset .= "\x1B\x51" . chr($paperWidthSetting->charsPerLine); // Reset print region to full width

        return $content . $reset;
    }

    private function magnify(Magnify $size, callable $transformChildren, PaperWidthSetting $paperWidthSetting): string
    {
        $width = max(1, min(6, $size->width));
        if ($size->height !== null) {
            $height = max(1, min(6, $size->height));
        } else {
            $height = $width;
        }

        $oldCharsPerLineMultiplier = $paperWidthSetting->charsPerLineMultiplier;
        $paperWidthSetting->charsPerLineMultiplier = $width;

        $commandWidth  = "\x1B\x69" . chr($width - 1) . chr($width - 1);
        $commandHeight = "\x1B\x68" . chr($height - 1);

        $output = $commandWidth . $commandHeight
            . $transformChildren($size)
            . "\x1B\x69\x00\x00"
            . "\x1B\x68\x00";

        $paperWidthSetting->charsPerLineMultiplier = $oldCharsPerLineMultiplier;

        return $output;
    }

    private function font(Font $font, callable $transformChildren): string
    {
        $fontByte = match($font->type) {
            FontType::A => 0, // 12x24 dots
            FontType::B => 1, // 9x24 dots
            FontType::C => 2, // 9x17 dots
        };

        return "\x1B\x1E\x46" . chr($fontByte)
            . $transformChildren($font)
            . "\x1B\x1E\x46\x00";
    }

    private function twoColumns(Column $command, callable $transformChildren, callable $normalizeText, PrintSettings $printSettings): string
    {
        /** @var PaperWidthSetting $paperWidthSetting */
        $paperWidthSetting =  $printSettings->get(PaperWidthSetting::class);

        $effectiveWidth = (int)floor($paperWidthSetting->charsPerLine / $paperWidthSetting->charsPerLineMultiplier);
        $minPadding = 2;
        $wrapIndent = 2;

        $leftText  = implode('', array_map(fn($c) => $this->flattenText($c), $command->leftChildren));
        $rightText = implode('', array_map(fn($c) => $this->flattenText($c), $command->rightChildren));

        $rightLen = mb_strlen($rightText);
        $maxLeftWidth = max(1, $effectiveWidth - $rightLen - $minPadding);
        $leftLines = $this->wrapText($leftText, $maxLeftWidth);

        $transformChild = fn(Command $child) => $this->transform(
            $child,
            $transformChildren,
            $normalizeText,
            $printSettings,
        );

        $leftBytes  = str_replace("\n", '', implode('', array_map($transformChild, $command->leftChildren)));
        $rightBytes = str_replace("\n", '', implode('', array_map($transformChild, $command->rightChildren)));

        $charMap = [];
        $pos = 0;
        $len = mb_strlen($leftBytes);
        for ($i = 0; $i < $len; $i++) {
            $charMap[] = $pos;
            $pos += mb_strlen(mb_substr($leftBytes, $i, 1, 'UTF-8'), '8bit');
        }
        $charMap[] = $pos;

        $output = '';
        $offset = 0;

        foreach ($leftLines as $i => $line) {
            $lineLen = mb_strlen($line);
            $start = $charMap[$offset] ?? 0;
            $end = $charMap[$offset + $lineLen] ?? strlen($leftBytes);
            $lineBytes = mb_substr($leftBytes, $start, $end - $start, '8bit');

            $offset += $lineLen;
            if ($i < count($leftLines) - 1) $offset++;

            if ($i === 0) {
                $padding = max($minPadding, $effectiveWidth - $rightLen - mb_strlen($line));
                $output .= $lineBytes . str_repeat($command->spacer, $padding) . $rightBytes . "\n";
            } else {
                $output .= str_repeat(' ', $wrapIndent) . $lineBytes . "\n";
            }

            $rightBytes = '';
            $rightLen = 0;
        }

        return $output;
    }

    private function wrapText(string $text, int $maxWidth): array
    {
        $lines = [];
        $currentLine = '';

        // Split by any character (spaces included)
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($chars as $char) {
            if (mb_strlen($currentLine . $char) <= $maxWidth) {
                $currentLine .= $char;
            } else {
                $lines[] = $currentLine;
                $currentLine = $char;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines;
    }


    private function flattenText(Command $command): string
    {
        return match (true) {
            $command instanceof Text => $command->value,
            $command instanceof Column => $this->flattenText($command->leftChildren) . $this->flattenText($command->rightChildren),
            $command instanceof ContainerCommand => implode('', array_map(fn($c) => $this->flattenText($c), $command->children())),
            default => '',
        };
    }
}
