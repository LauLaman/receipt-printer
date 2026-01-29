<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Settings;

use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;

final readonly class PrinterModelSetting implements PrintSetting
{
    public function __construct(public PrinterModel $model) {}
}