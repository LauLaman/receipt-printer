# API Reference

## Table of Contents

* [Paper Commands](#paper-commands)
* [Barcode / QR Code Commands](#barcode--qr-code-commands)
* [Layout Commands](#layout-commands)
* [Graphics Commands](#graphics-commands)

---

## Paper Commands

### `LauLaman\ReceiptPrinter\Domain\Command\Paper\Cut`

Cuts the paper and optionally feeds it forward. Can perform a full or partial cut depending on printer support.

| Command                                                               | Builder                                                               | Result                     |
| --------------------------------------------------------------------- | --------------------------------------------------------------------- | -------------------------- |
| <pre><code class="language-php">new Cut();</code></pre>               | <pre><code class="language-php">$b->cut();</code></pre>               | Feed and cut paper         |
| <pre><code class="language-php">new Cut(feed: false);</code></pre>    | <pre><code class="language-php">$b->cut(feed: false);</code></pre>    | Just cut paper             |
| <pre><code class="language-php">new Cut(partial: false);</code></pre> | <pre><code class="language-php">$b->cut(partial: false);</code></pre> | Feed and partial cut paper |

### `LauLaman\ReceiptPrinter\Domain\Command\Paper\Feed`

Feeds the paper a specified number of lines without cutting.

| Command                                                   | Builder                                                   | Result                |
| --------------------------------------------------------- | --------------------------------------------------------- | --------------------- |
| <pre><code class="language-php">new Feed();</code></pre>  | <pre><code class="language-php">$b->feed();</code></pre>  | Feed paper by 1 line  |
| <pre><code class="language-php">new Feed(3);</code></pre> | <pre><code class="language-php">$b->feed(3);</code></pre> | Feed paper by 3 lines |

### `LauLaman\ReceiptPrinter\Domain\Command\Paper\OpenDrawer`

Opens the cash drawer connected to the printer.

| Command                                                        | Builder                                                        | Result           |
| -------------------------------------------------------------- | -------------------------------------------------------------- | ---------------- |
| <pre><code class="language-php">new OpenDrawer();</code></pre> | <pre><code class="language-php">$b->openDrawer();</code></pre> | Open cash drawer |

### `LauLaman\ReceiptPrinter\Domain\Command\Paper\PrintDensity`

Adjusts the print density (darker or lighter) for the printer (-3 to +3).

| Command                                                            | Builder                                                            | Result                 |
| ------------------------------------------------------------------ | ------------------------------------------------------------------ | ---------------------- |
| <pre><code class="language-php">new PrintDensity();</code></pre>   | <pre><code class="language-php">$b->printDensity();</code></pre>   | Default density (0)    |
| <pre><code class="language-php">new PrintDensity(2);</code></pre>  | <pre><code class="language-php">$b->printDensity(2);</code></pre>  | Increase print density |
| <pre><code class="language-php">new PrintDensity(-1);</code></pre> | <pre><code class="language-php">$b->printDensity(-1);</code></pre> | Decrease print density |

### `LauLaman\ReceiptPrinter\Domain\Command\Paper\PrintSpeed`

Sets the printing speed of the printer.

| Command                                                                         | Builder                                                                         | Result               |
| ------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- | -------------------- |
| <pre><code class="language-php">new PrintSpeed();</code></pre>                  | <pre><code class="language-php">$b->printSpeed();</code></pre>                  | Default speed (HIGH) |
| <pre><code class="language-php">new PrintSpeed(PrinterSpeed::LOW);</code></pre> | <pre><code class="language-php">$b->printSpeed(PrinterSpeed::LOW);</code></pre> | Slow printing        |

---

## Barcode / QR Code Commands

### `LauLaman\ReceiptPrinter\Domain\Command\Barcode\Barcode`

Prints a standard barcode with configurable type, width, height, and human-readable text.

| Command                                                                                                | Builder                                                                                                 | Result                                   |
| ------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| <pre><code class="language-php">new Barcode("123456");</code></pre>                                     | <pre><code class="language-php">$b->barcode("123456");</code></pre>                                     | CODE39 barcode with default width/height |
| <pre><code class="language-php">new Barcode("123456", BarcodeType::CODE128, 3, 100, true);</code></pre> | <pre><code class="language-php">$b->barcode("123456", BarcodeType::CODE128, 3, 100, true);</code></pre> | Custom barcode                           |

### `LauLaman\ReceiptPrinter\Domain\Command\Barcode\GS1DataBarExpanded`

Prints a GS1 DataBar Expanded barcode.

| Command                                                                                   | Builder                                                                            | Result              |
| ----------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- | ------------------- |
| <pre><code class="language-php">new GS1DataBarExpanded("012345678901234567");</code></pre> | <pre><code class="language-php">$b->gs1DataBar("012345678901234567");</code></pre> | GS1 DataBar barcode |

### `LauLaman\ReceiptPrinter\Domain\Command\Barcode\Pdf417`

Prints a PDF417 2D barcode with ECC and size configuration.

| Command                                                                           | Builder                                                                            | Result         |
| --------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- | -------------- |
| <pre><code class="language-php">new Pdf417("Data");</code></pre>                   | <pre><code class="language-php">$b->pdf417("Data");</code></pre>                   | PDF417 barcode |
| <pre><code class="language-php">new Pdf417("Data", 3, 2, 3, 0, 1, 2);</code></pre> | <pre><code class="language-php">$b->pdf417("Data", 3, 2, 3, 0, 1, 2);</code></pre> | Custom PDF417  |

### `LauLaman\ReceiptPrinter\Domain\Command\Barcode\QRCode`

Prints a QR code with configurable size and error correction.

| Command                                                                                         | Builder                                                                                          | Result                   |
| ----------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ | ------------------------ |
| <pre><code class="language-php">new QRCode("WIFI:T:nopass;S:SSID;;");</code></pre>               | <pre><code class="language-php">$b->qrCode("WIFI:T:nopass;S:SSID;;");</code></pre>               | Default size & MEDIUM EC |
| <pre><code class="language-php">new QRCode("Data", 8, QRCodeErrorCorrection::HIGH);</code></pre> | <pre><code class="language-php">$b->qrCode("Data", 8, QRCodeErrorCorrection::HIGH);</code></pre> | Large QR with high EC    |

---

## Layout Commands

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Align`
Centers or aligns child text/commands.

| Command | Builder | Result |
|---------|---------|--------|
| <pre><code class="language-php">new Align(Alignment::CENTER, new Text('Hello'));</code></pre> | <pre><code class="language-php">$b->align(Alignment::CENTER)->text('Hello')->end();</code></pre> | Centered text block |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Bold`
Makes contained text bold.

| Command                                                                                                           | Builder | Result |
|-------------------------------------------------------------------------------------------------------------------|---------|--------|
| <pre><code class="language-php">new Bold(new Text('Hello'));</code></pre>                                          | <pre><code class="language-php">$b->bold('Hello');</code></pre> | Bold text |
| <pre><code class="language-php">new Bold(<br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->bold()<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Multi-line bold text |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Invert`
Inverts text (white-on-black).

| Command                                                                                                             | Builder | Result |
|---------------------------------------------------------------------------------------------------------------------|---------|--------|
| <pre><code class="language-php">new Invert(new Text('Hello'));</code></pre>                                          | <pre><code class="language-php">$b->invert('Hello');</code></pre> | White-on-black text |
| <pre><code class="language-php">new Invert(<br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->invert()<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Multi-line inverted text |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Underline`
Underlines contained text.

| Command                                                                                                                | Builder | Result |
|------------------------------------------------------------------------------------------------------------------------|---------|--------|
| <pre><code class="language-php">new Underline(new Text('Hello'));</code></pre>                                          | <pre><code class="language-php">$b->underline('Hello');</code></pre> | Underlined text |
| <pre><code class="language-php">new Underline(<br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->underline()<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Multi-line underlined text |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Upperline`
Draws a line above text.

| Command                                                                                                                | Builder | Result |
|------------------------------------------------------------------------------------------------------------------------|---------|--------|
| <pre><code class="language-php">new Upperline(new Text('Hello'));</code></pre>                                          | <pre><code class="language-php">$b->upperline('Hello');</code></pre> | Line above text |
| <pre><code class="language-php">new Upperline(<br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->upperline()<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Multi-line upperline text |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\UpsideDown`
Rotates text upside down.

| Command                                                                                                                 | Builder | Result |
|-------------------------------------------------------------------------------------------------------------------------|---------|--------|
| <pre><code class="language-php">new UpsideDown(new Text('Hello'));</code></pre>                                          | <pre><code class="language-php">$b->upsideDown('Hello');</code></pre> | Rotated text |
| <pre><code class="language-php">new UpsideDown(<br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->upsideDown()<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Multi-line rotated text |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Magnify`
Enlarges text horizontally and/or vertically.

| Command                                                                                                                    | Builder                                                                                                                    | Result                                    |
|----------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------|-------------------------------------------|
| <pre><code class="language-php">new Magnify(2, null, new Text('Hello'));</code></pre>                                      | <pre><code class="language-php">$b->magnify(2, text: 'Hello');</code></pre>                                                | Enlarged text by 2x                       |
| <pre><code class="language-php">new Magnify(2, 3, <br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->magnify(2, 3)<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Enlarged text 2x in with and 3x in height |
| <pre><code class="language-php">new Magnify(1, 2, <br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->magnify(1, 2)<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Enlarged text 1x in with and 2x in height |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Font`
Changes font type.

| Command                                                                                                                             | Builder                                                                                                                             | Result |
|-------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------|--------|
| <pre><code class="language-php">new Font(FontType::FONT_B, new Text('Hello'));</code></pre>                                          | <pre><code class="language-php">$b->font(FontType::FONT_B, 'Hello');</code></pre>                                    | Text in FONT_B |
| <pre><code class="language-php">new Font(FontType::FONT_B, <br>    new Text('Line 1'),<br>    new Text('Line 2')<br>);</code></pre> | <pre><code class="language-php">$b->font(FontType::FONT_B)<br>    ->text('Line 1')<br>    ->text('Line 2')<br>->end();</code></pre> | Multi-line text in FONT_B |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Text`
Prints a single line of text (leaf command).

| Command | Builder | Result |
|---------|---------|--------|
| <pre><code class="language-php">new Text('Hello');</code></pre> | <pre><code class="language-php">$b->text('Hello');</code></pre> | Leaf text |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Separator`
Prints a horizontal separator line.

| Command | Builder | Result |
|---------|---------|--------|
| <pre><code class="language-php">new Separator();</code></pre> | <pre><code class="language-php">$b->separator();</code></pre> | Separator line |

---

### `LauLaman\ReceiptPrinter\Domain\Command\Layout\Column`
Prints text in two columns.

| Command                                                                                                                  | Builder | Result |
|--------------------------------------------------------------------------------------------------------------------------|---------|--------|
| <pre><code class="language-php">new Column(<br>    [new Text('1 x Item')],<br>    [new Text('€4.50')]<br>);</code></pre> | <pre><code class="language-php">$b->column([new Text('1 x Item')], [new Text('€4.50')]);</code></pre> | Two-column layout |

---

## Graphics Commands

### `LauLaman\ReceiptPrinter\Domain\Command\Graphics\Logo`

Prints a stored printer logo.

| Command           | Builder           | Result                      |
| ----------------- | ----------------- | --------------------------- |
| `new Logo(2);`    | `$b->logo(2);`    | Logo #2 printed             |
| `new Logo(3, 2);` | `$b->logo(3, 2);` | Logo #3 printed at 2x scale |
