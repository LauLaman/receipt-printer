<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Settings;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Enum\CodePage;

final readonly class CodePageSetting implements PrintSetting, Command
{
    public function __construct(
        public CodePage $codePage = CodePage::CP_858,
    ) {
    }
}
