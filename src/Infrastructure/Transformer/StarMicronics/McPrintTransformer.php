<?php

declare(strict_types=1);

namespace LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\ContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Normalizer\TextNormalizer;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;
use LauLaman\ReceiptPrinter\Domain\Transformer\PrinterTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\BarcodeTransformer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\GraphicsTransformer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\PaperTransformer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\SettingsTransformer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\SoundTransformer;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\LayoutTransformer;

class McPrintTransformer implements PrinterTransformer
{
    /** @var CommandTransformer[] */
    private array $subTransformers;

    private PrintSettings $printSettings;

    public function __construct(
        private readonly TextNormalizer $textNormalizer,
        CommandTransformer ...$subTransformers

    ) {
        $this->subTransformers = $subTransformers;
    }

    public static function create(TextNormalizer $textNormalizer): self
    {
        return new self(
            $textNormalizer,
            new SettingsTransformer(),
            new LayoutTransformer(),
            new PaperTransformer(),
            new BarcodeTransformer(),
            new GraphicsTransformer(),
            new SoundTransformer(),
        );
    }

    public function supports(PrinterModel $model): bool
    {
        return $model === PrinterModel::STAR_MC_PRINT2 || $model === PrinterModel::STAR_MC_PRINT3 || $model === PrinterModel::STAR_TSP650_II;
    }

    /**
     * @param PrintSettings $printSettings
     * @param Command ...$commands
     * @return string
     */
    public function transform(PrintSettings $printSettings, Command ...$commands): string
    {
        $this->printSettings = $printSettings;

        $prefix = '';
        $body   = '';

        foreach ($printSettings->all() as $setting) {
            if ($setting instanceof Command) {
                $prefix .= $this->transformCommand($setting);
            }
        }

        foreach ($commands as $command) {
            $body .= $this->transformCommand($command);
        }

        return $prefix . $body;
    }

    private function transformCommand(Command $command): string
    {
        foreach ($this->subTransformers as $t) {
            if ($t->supports($command)) {
                return $t->transform(
                    $command,
                    fn($command) => $command instanceof ContainerCommand
                        ? $this->children($command)
                        : $this->transformCommand($command),
                    fn($text) => $this->textNormalizer->normalize($text, $this->printSettings),
                    $this->printSettings
                );
            }
        }

        throw new \LogicException('Unsupported command ' . $command::class);
    }

    private function children(ContainerCommand $command): string
    {
        return implode('', array_map($this->transformCommand(...), $command->children()));
    }
}