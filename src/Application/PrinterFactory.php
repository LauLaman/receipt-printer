<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Domain\Contract\PrinterDriverInterface;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModelInterface;
use LauLaman\ReceiptPrinter\Domain\PrinterSettings;
use LauLaman\ReceiptPrinter\StarMicronics\Driver\StarMicronicsPrinterDriverFactory;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\BluetoothTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\CupsTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\IppTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\SocketTransportInterface;
use LauLaman\ReceiptPrinter\Infrastructure\Transport\UsbTransportInterface;
use LogicException;

final readonly class PrinterFactory
{

    /** @var PrinterDriverInterface[] */
    private array $printerDrivers;

    public function __construct(
        PrinterDriverInterface ...$printerDrivers,
    ) {
        $this->printerDrivers = $printerDrivers;
    }

    /**
     * @deprecated inject all  PrinterDriverInterface[] in constructor
     */
    public static function create(): PrinterFactory
    {
        return new self(...[
            StarMicronicsPrinterDriverFactory::create()
        ]);
    }

    /**
     * Build a printer with socket/network transport
     */
    public function socket(
        PrinterSettings $settings,
        string $host,
        int $port = 9100
    ): Printer {
        $transformer = $this->getDriver($settings->model);
        $transport = new SocketTransportInterface($host, $port);

        return new Printer($settings, $transformer, $transport);
    }

    /**
     * Build a printer with USB transport (Linux)
     */
    public function usb(
        PrinterSettings $settings,
        string $device = '/dev/usb/lp0'
    ): Printer {
        $transformer = $this->getDriver($settings->model);
        $transport = new UsbTransportInterface($device);

        return new Printer($settings, $transformer, $transport);
    }

    /**
     * Build a printer with CUPS transport (macOS/Linux)
     */
    public function cups(
        PrinterSettings $settings,
        string $printerName
    ): Printer {
        $transformer = $this->getDriver($settings->model);
        $transport = new CupsTransportInterface($printerName);

        return new Printer($settings, $transformer, $transport);
    }

    /**
     * Build a printer with Bluetooth transport
     */
    public function bluetooth(
        PrinterSettings $settings,
        string $address,
        int $channel = 1
    ): Printer {
        $transformer = $this->getDriver($settings->model);
        $transport = new BluetoothTransportInterface($address, $channel);

        return new Printer($settings, $transformer, $transport);
    }

    /**
     * Build a printer with IPP transport
     */
    public function Ipp(
        PrinterSettings $settings,
        string $serial,
    ): Printer {
        $transformer = $this->getDriver($settings->model);
        $transport = new IppTransportInterface($serial);

        return new Printer($settings, $transformer, $transport);
    }

    /**
     * Get the appropriate transformer for the printer model
     */
    private function getDriver(PrinterModelInterface $model): PrinterDriverInterface
    {
        foreach ($this->printerDrivers as $printerTransformer) {
            if ($printerTransformer->supports($model)) {
                return $printerTransformer;
            }
        }

        throw new LogicException("No transformer implemented for model: {$model->name}");
    }
}