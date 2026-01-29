<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSetting;

final readonly class Receipt
{
    /**
     * @param array<PrintSetting> $settings
     * @param array<Command> $commands
     */
    public function __construct(
        private array $settings,
        private array $commands
    ) {
    }

    /**
     * @return array<PrintSetting>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return array<Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
