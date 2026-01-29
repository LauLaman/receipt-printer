<?php

declare(strict_types=1);

namespace Tests\Unit\LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint;

use LauLaman\ReceiptPrinter\Domain\Command\Command;
use LauLaman\ReceiptPrinter\Domain\Command\Sound\SoundCommand;
use LauLaman\ReceiptPrinter\Domain\Settings\PrintSettings;
use LauLaman\ReceiptPrinter\Domain\Command\Sound\Buzzer;
use LauLaman\ReceiptPrinter\Domain\Command\Sound\MelodySpeaker;
use LauLaman\ReceiptPrinter\Domain\Enum\BuzzerPattern;
use LauLaman\ReceiptPrinter\Infrastructure\Transformer\StarMicronics\McPrint\SoundTransformer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SoundTransformerTest extends TestCase
{
    private SoundTransformer $transformer;
    private PrintSettings $settings;

    protected function setUp(): void
    {
        $this->transformer = new SoundTransformer();
        $this->settings = new PrintSettings();
    }

    #[Test]
    public function itSupportsSoundCommands(): void
    {
        $command = $this->createMock(SoundCommand::class);
        $this->assertTrue($this->transformer->supports($command));
    }

    #[Test]
    public function itPlaysSimpleBuzzer(): void
    {
        $cmd = new Buzzer(BuzzerPattern::SIMPLE);
        $result = $this->transformer->transform($cmd, fn($c) => '', fn($t) => $t, $this->settings);
        $this->assertSame("\x07", $result);
    }

    #[Test]
    public function itPlaysTimedBuzzer(): void
    {
        $cmd = new Buzzer(BuzzerPattern::PATTERN, onTimeMs: 200, offTimeMs: 100);
        $result = $this->transformer->transform($cmd, fn($c) => '', fn($t) => $t, $this->settings);

        // onTime = 200ms / 50 = 4 -> \x04, offTime = 100ms / 50 = 2 -> \x02
        $expected = "\x1B\x07\x04\x02\x07";
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function itPlaysMelodySpeaker(): void
    {
        $cmd = new MelodySpeaker(
            notes: [
                ['pitch' => 60, 'duration' => 400, 'volume' => 10],
                ['pitch' => 64, 'duration' => 200, 'volume' => 8],
            ],
            repeat: 2
        );

        $result = $this->transformer->transform($cmd, fn($c) => '', fn($t) => $t, $this->settings);

        $data = "\x1B\x1D\x73\x52\x00";   // Header
        $data .= "\x02\x02\x00\x00";       // Note count 2, repeat 2
        $data .= "\x3C\x04\x0A";           // Note 1
        $data .= "\x40\x02\x08";           // Note 2

        $this->assertSame($data, $result);
    }

    #[Test]
    public function itThrowsIfMelodySpeakerHasNoNotes(): void
    {
        $cmd = new MelodySpeaker(notes: [], repeat: 2);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('MelodySpeaker command must have at least one note.');

        $this->transformer->transform($cmd, fn($c) => '', fn($t) => $t, $this->settings);
    }

    #[Test]
    public function itThrowsForUnsupportedCommand(): void
    {
        $this->expectException(\LogicException::class);
        $unsupported = $this->createMock(Command::class);
        $this->transformer->transform($unsupported, fn($c) => '', fn($t) => $t, $this->settings);
    }
}
