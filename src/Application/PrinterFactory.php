<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Domain\Transformer\PrinterTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Infrastructure\Normalizer\StarMicronics\McPrintTextNormalizer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrintTransformer;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\BluetoothTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\CupsTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\IppTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\SocketTransport;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\UsbTransport;

final readonly class PrinterFactory
{

    /** @var PrinterTransformer[] */
    private array $printerTransformers;

    public function __construct(
        McPrintTransformer $mcPrintTransformer,
    ) {
        $this->printerTransformers = [$mcPrintTransformer];
    }

    public static function create(): PrinterFactory
    {
        return new self(McPrintTransformer::create(new McPrintTextNormalizer()));
    }

    /**
     * Build a printer with socket/network transport
     */
    public function socket(
        PrinterModel $model,
        PaperWidth $paper,
        string $host,
        int $port = 9100
    ): Printer {
        $transformer = $this->getTransformer($model);
        $transport = new SocketTransport($host, $port);

        return new Printer($model, $paper, $transformer, $transport);
    }

    /**
     * Build a printer with USB transport (Linux)
     */
    public function usb(
        PrinterModel $model,
        PaperWidth $paper,
        string $device = '/dev/usb/lp0'
    ): Printer {
        $transformer = $this->getTransformer($model);
        $transport = new UsbTransport($device);

        return new Printer($model, $paper, $transformer, $transport);
    }

    /**
     * Build a printer with CUPS transport (macOS/Linux)
     */
    public function cups(
        PrinterModel $model,
        PaperWidth $paper,
        string $printerName
    ): Printer {
        $transformer = $this->getTransformer($model);
        $transport = new CupsTransport($printerName);

        return new Printer($model, $paper, $transformer, $transport);
    }

    /**
     * Build a printer with Bluetooth transport
     */
    public function bluetooth(
        PrinterModel $model,
        PaperWidth $paper,
        string $address,
        int $channel = 1
    ): Printer {
        $transformer = $this->getTransformer($model);
        $transport = new BluetoothTransport($address, $channel);

        return new Printer($model, $paper, $transformer, $transport);
    }

    /**
     * Build a printer with IPP transport
     */
    public function Ipp(
        PrinterModel $model,
        PaperWidth $paper,
        string $serial,
    ): Printer {
        $transformer = $this->getTransformer($model);
        $transport = new IppTransport($serial);

        return new Printer($model, $paper, $transformer, $transport);
    }

    /**
     * Get the appropriate transformer for the printer model
     */
    private function getTransformer(PrinterModel $model): PrinterTransformer
    {
        foreach ($this->printerTransformers as $printerTransformer) {
            if ($printerTransformer->supports($model)) {
                return $printerTransformer;
            }
        }

        throw new \LogicException("No transformer implemented for model: {$model->name}");
    }
}