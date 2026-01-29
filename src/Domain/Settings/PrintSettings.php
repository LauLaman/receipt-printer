<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Settings;

use InvalidArgumentException;

final class PrintSettings
{
    /**
     * @var array<PrintSetting>
     */
    private array $settings;

    public function add(PrintSetting ...$settings): void
    {
        foreach ($settings as $setting) {
            if (isset($this->settings[$setting::class])) {
                throw new InvalidArgumentException(
                    "Setting ".$setting::class." is already set. You can only set print settings once"
                );
            }
            $this->settings[$setting::class] = $setting;
        }
    }

    public function get(string $setting): ?PrintSetting
    {
        return $this->settings[$setting] ?? null;
    }

    /** @return PrintSetting[] */
    public function all(): array
    {
        return $this->settings;
    }
}