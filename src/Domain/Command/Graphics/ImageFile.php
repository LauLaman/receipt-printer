<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Graphics;

final readonly class ImageFile implements GraphicsCommand
{
    public function __construct(
        public string $filePath,
        public int $width,
        public int $height,
        public ?int $scale = null
    ) {}
}
