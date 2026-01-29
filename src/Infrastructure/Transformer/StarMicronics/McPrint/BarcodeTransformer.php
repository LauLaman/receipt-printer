<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Barcode;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\BarcodeCommand;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\GS1DataBarExpanded;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\Pdf417;
use LauLaman\ReceiptPrinter\Domain\Command\Barcode\QRCode;
use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;
use LauLaman\ReceiptPrinter\Domain\Enum\QRCodeErrorCorrection;

class BarcodeTransformer implements CommandTransformer
{
    public function supports(Command $command): bool
    {
        return $command instanceof BarcodeCommand;
    }

    public function transform(
        Command $command,
        callable $transformChildren,
        callable $normalizeText,
        PrintSettings $printSettings
    ): string {
        return match (true) {
            $command instanceof Barcode => $this->barcode($command),
            $command instanceof QRCode => $this->qrCode($command),
            $command instanceof Pdf417 => $this->pdf417($command),
            $command instanceof GS1DataBarExpanded => $this->gs1DataBarExpanded($command),
            default => throw new \LogicException('Unsupported barcode command ' . $command::class),
        };
    }

    private function barcode(Barcode $command): string
    {
        $type = match($command->type) {
            BarcodeType::UPC_E => 0,
            BarcodeType::UPC_A => 1,
            BarcodeType::EAN8 => 2,
            BarcodeType::EAN13 => 3,
            BarcodeType::CODE39 => 4,
            BarcodeType::ITF => 5,
            BarcodeType::CODE128 => 6,
            BarcodeType::CODE93 => 7,
        };

        return "\x1B\x62"
            . chr($type)
            . chr($command->hri ? 2 : 0)
            . chr(max(1, min(8, $command->width)))
            . chr(max(1, min(255, $command->height)))
            . $command->data
            . "\x1E\n";
    }

    private function qrCode(QRCode $command): string
    {
        $size = max(1, min(8, $command->size));

        $ec = match ($command->errorCorrection) {
            QRCodeErrorCorrection::LOW      => 0,
            QRCodeErrorCorrection::MEDIUM   => 1,
            QRCodeErrorCorrection::QUARTILE => 2,
            QRCodeErrorCorrection::HIGH     => 3,
        };

        $dataLen = strlen($command->data);
        $nL = $dataLen & 0xFF;
        $nH = ($dataLen >> 8) & 0xFF;

        $seq = '';

        // Set QR model = Model 2
        $seq .= "\x1B\x1D\x79\x53\x30\x02";

        // Set error correction
        $seq .= "\x1B\x1D\x79\x53\x31" . chr($ec);

        // Set cell size
        $seq .= "\x1B\x1D\x79\x53\x32" . chr($size);

        // Set data (AUTO, m = 0)
        $seq .= "\x1B\x1D\x79\x44\x31\x00"
            . chr($nL)
            . chr($nH)
            . $command->data;

        // Print QR
        $seq .= "\x1B\x1D\x79\x50\n";

        return $seq;
    }

    private function pdf417(Pdf417 $command): string
    {
        $len = strlen($command->data);
        if ($len < 1 || $len > 1024) {
            throw new \LogicException('PDF417 data length must be 1–1024 bytes');
        }

        $nL = $len & 0xFF;
        $nH = ($len >> 8) & 0xFF;

        $seq = '';

        // ESC GS x S 0 n p1 p2 — size
        $seq .= "\x1B\x1D\x78\x53\x30"
            . chr($command->sizeMode)
            . chr($command->verticalWeight)
            . chr($command->horizontalWeight);

        // ESC GS x S 1 n — ECC
        $seq .= "\x1B\x1D\x78\x53\x31" . chr($command->ecc);

        // ESC GS x S 2 n — module X size
        $seq .= "\x1B\x1D\x78\x53\x32" . chr($command->moduleX);

        // ESC GS x S 3 n — aspect ratio
        $seq .= "\x1B\x1D\x78\x53\x33" . chr($command->aspect);

        // ESC GS x D nL nH data
        $seq .= "\x1B\x1D\x78\x44"
            . chr($nL)
            . chr($nH)
            . $command->data;

        // ESC GS x P — print
        $seq .= "\x1B\x1D\x78\x50\n";

        return $seq;
    }

    private function gs1DataBarExpanded(GS1DataBarExpanded $command): string
    {
        $data = $command->data;
        $k = strlen($data);
        if ($k < 2 || $k > 255) {
            throw new \LogicException('GS1 Expanded data length invalid (2–255 bytes)');
        }

        // Store data in symbol saving region (fn=80)
        $seq = $this->gsK(51, 80, "\x30\x4C" . $data, true);

        // Print the symbol (fn=81)
        $seq .= $this->gsK(51, 81, "\x30", true);


        return $seq . "\n";
    }

    private function gsK(int $cn, int $fn, string $params): string
    {
        $len = strlen($params) + 2;

        $pL = $len & 0xFF;
        $pH = ($len >> 8) & 0xFF;

        return "\x1B\x1D\x28\x6B" . chr($pL) . chr($pH) . chr($cn) . chr($fn) . $params;
    }
}
