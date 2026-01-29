<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics;


use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\ContainerCommand;
use LauLaman\ReceiptPrinter\Domain\Normalizer\TextNormalizer;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSetting;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Transformer\CommandTransformer;
use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrintTransformer;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class McPrintTransformerTest extends TestCase
{
    #[Test]
    public function itSupportsStarMcPrintModels(): void
    {
        $transformer = new McPrintTransformer($this->createMock(TextNormalizer::class));

        foreach (PrinterModel::cases() as $model) {
            $this->assertTrue($transformer->supports($model));
        }
    }

    #[Test]
    public function itDelegatesToFirstSupportingSubTransformer(): void
    {
        $command = $this->createMock(Command::class);

        $settings = new PrintSettings();
        $dummySetting = $this->createMock(PrintSetting::class);
        $settings->add($dummySetting);

        $first = $this->createMock(CommandTransformer::class);
        $second = $this->createMock(CommandTransformer::class);

        $first->method('supports')->willReturn(false);
        $second->method('supports')->willReturn(true);
        $second->expects($this->once())
            ->method('transform')
            ->willReturn('OK');

        $transformer = new McPrintTransformer(
            $this->createMock(TextNormalizer::class),
            $first,
            $second
        );

        $result = $transformer->transform($settings, $command);
        $this->assertSame('OK', $result);
    }

    #[Test]
    public function itTransformsContainerCommandsRecursively(): void
    {
        $child1 = $this->createMock(Command::class);
        $child2 = $this->createMock(Command::class);

        $container = $this->createMock(ContainerCommand::class);
        $container->method('children')->willReturn([$child1, $child2]);

        $settings = new PrintSettings();
        $settings->add($this->createMock(PrintSetting::class));

        $subTransformer = $this->createMock(CommandTransformer::class);
        $subTransformer->method('supports')->willReturn(true);
        $subTransformer->method('transform')
            ->willReturnCallback(
                fn(Command $cmd, callable $children, callable $text) =>
                $cmd instanceof ContainerCommand ? '[' . $children($cmd) . ']' : 'X'
            );

        $transformer = new McPrintTransformer($this->createMock(TextNormalizer::class), $subTransformer);

        $result = $transformer->transform($settings, $container);
        $this->assertSame('[XX]', $result);
    }

    #[Test]
    public function itUsesTextNormalizer(): void
    {
        $command = $this->createMock(Command::class);

        $settings = new PrintSettings();
        $settings->add($this->createMock(PrintSetting::class));

        $normalizer = $this->createMock(TextNormalizer::class);
        $normalizer->expects($this->once())
            ->method('normalize')
            ->with('hello', $settings)
            ->willReturn('HELLO');

        $subTransformer = $this->createMock(CommandTransformer::class);
        $subTransformer->method('supports')->willReturn(true);
        $subTransformer->method('transform')
            ->willReturnCallback(fn(Command $cmd, callable $children, callable $text) => $text('hello'));

        $transformer = new McPrintTransformer($normalizer, $subTransformer);

        $result = $transformer->transform($settings, $command);
        $this->assertSame('HELLO', $result);
    }

    #[Test]
    public function itThrowsWhenNoSubTransformerSupportsCommand(): void
    {
        $command = $this->createMock(Command::class);

        $settings = new PrintSettings();
        $settings->add($this->createMock(PrintSetting::class));

        $subTransformer = $this->createMock(CommandTransformer::class);
        $subTransformer->method('supports')->willReturn(false);

        $transformer = new McPrintTransformer(
            $this->createMock(TextNormalizer::class),
            $subTransformer
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unsupported command');

        $transformer->transform($settings, $command);
    }
}

