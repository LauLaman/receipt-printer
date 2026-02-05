<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Domain\Command\Draw;

final readonly class ImageFile implements DrawCommand
{
    public function __construct(
        public string $filePath,
        public int $width,
        public int $height,
        public ?int $scale = null
    ) {}
}
