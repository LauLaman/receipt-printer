<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Application;

use LauLaman\ReceiptPrinter\Application\Printer;
use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Contract\PrinterDriverInterface;
use LauLaman\ReceiptPrinter\Domain\Contract\PrinterTransportInterface;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrinterModelSetting;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrintSetting\PrintSetting;
use LauLaman\ReceiptPrinter\Domain\PrinterSetting\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Receipt;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PrintSetting\PrintableAreaSetting;

final class PrinterTest extends TestCase
{
    private PrinterModel $model;
    private PaperWidth $paper;
    private PrinterDriverInterface $transformer;
    private PrinterTransportInterface $transport;
    private Printer $printer;

    protected function setUp(): void
    {
        $this->model = PrinterModel::STAR_MC_PRINT2;
        $this->paper = PaperWidth::SMALL;

        $this->transformer = $this->createMock(PrinterDriverInterface::class);
        $this->transport = $this->createMock(PrinterTransportInterface::class);

        $this->printer = new Printer(
            $this->model,
            $this->paper,
            $this->transformer,
            $this->transport
        );
    }

    #[Test]
    public function itSendsCommandsThroughTheTransformerAndTransport(): void
    {
        $command = $this->createMock(Command::class);

        $this->transformer
            ->expects($this->once())
            ->method('encode')
            ->with(
                $this->callback(function(PrintSettings $settings) {
                    $all = $settings->all();
                    return isset($all[PrinterModelSetting::class])
                        && isset($all[PrintableAreaSetting::class]);
                }),
                $command
            )
            ->willReturn('bytes-to-send');

        $this->transport
            ->expects($this->once())
            ->method('write')
            ->with('bytes-to-send');

        $this->printer->send([], $command);
    }

    #[Test]
    public function itPrintsReceiptsByForwardingCommandsToSend(): void
    {
        $command = $this->createMock(Command::class);

        $receipt = new Receipt(
            settings: [],        // no extra print settings for this test
            commands: [$command]
        );

        $this->transformer
            ->expects($this->once())
            ->method('encode')
            ->with(
                $this->isInstanceOf(PrintSettings::class),
                $command
            )
            ->willReturn('bytes');

        $this->transport
            ->expects($this->once())
            ->method('write')
            ->with('bytes');

        $this->printer->print($receipt);
    }

    #[Test]
    public function itIncludesExtraPrintSettingsWhenSending(): void
    {
        $command = $this->createMock(Command::class);
        $extraSetting = $this->createMock(PrintSetting::class);

        $this->transformer
            ->expects($this->once())
            ->method('encode')
            ->with(
                $this->callback(function(PrintSettings $settings) use ($extraSetting) {
                    $all = $settings->all();
                    return isset($all[PrinterModelSetting::class])
                        && isset($all[PrintableAreaSetting::class])
                        && in_array($extraSetting, $all, true);
                }),
                $command
            )
            ->willReturn('bytes');

        $this->transport
            ->expects($this->once())
            ->method('write')
            ->with('bytes');

        $this->printer->send([$extraSetting], $command);
    }
}
