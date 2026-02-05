# Receipt Printer

`laulaman/receipt-printer` is a PHP library for building and printing receipts on POS receipt printers. It will send raw printer instruction bytes to the printer.

### Features

* **Supports multiple printers and models** ([tested printers](./docs/tested-printers.md))
* **Builder-style API** with chaining ([api-reference](./docs/api-reference.md))
* **Text formatting**: bold, invert, alignment, text size, font type
* **Columns** for items and prices
* **Separator lines**, feed, and cut
* **Logos** (printer-stored)
* **QR codes** and barcodes (EAN13, CODE128, etc.)
* **Socket-based printing**

---

## Installation
```
composer require laulaman/receipt-printer
```

> Minimum PHP 8.2

---

## Usage

### 1. Create the factory

```php
use LauLaman\ReceiptPrinter\Application\PrinterFactory;use LauLaman\ReceiptPrinter\StarMicronics\Driver\Transformer\StarMicronicsPrinterDriver;use LauLaman\ReceiptPrinter\Infrastructure\Normalizer\StarMicronics\StarMicronicsPrinterTextNormalizer;

$printerFactory = PrinterFactory::create();

// Or construct it yourself:
$printerFactory = new PrinterFactory(
    new StarMicronicsPrinterDriver(
        new StarMicronicsPrinterTextNormalizer(),
        new SettingsTransformer(),
        new LayoutTransformer(),
        new PaperTransformer(),
        new BarcodeTransformer(),
        new GraphicsTransformer(),
        new SoundTransformer(),
    )
);
```

### 2. Connect to a Printer

Choose your transport type, select your printer and Paper size to get a Printer object from the factory

The following transport layers are bundled:
- BluetoothTransport
- CupsTransport
- IppTransport
- SocketTransport
- UsbTransport

You can also create your own transport layer by implementing `LauLaman\ReceiptPrinter\Domain\Transport\PrinterTransport`

> At this moment only the SocketTransport is actually tested since that is what I have. 
> 
> If you are able to confirm that other transports work, please let me know through a GitHub issue

```php

use LauLaman\ReceiptPrinter\Domain\Enum\PrinterModel;
use LauLaman\ReceiptPrinter\Domain\Enum\PaperWidth;

// Using a socket 
$printer = $printerFactory->socket(
    PrinterModel::STAR_MC_PRINT2,
    PaperWidth::SMALL,
    '192.168.0.12', // Printer IP
);

// Using USB
$printer = $printerFactory->usb(
    PrinterModel::STAR_MC_PRINT2,
    PaperWidth::SMALL,
    '/dev/usb/lp1',
);
```
---

### 3. Start printing!

Now you can start sending commands to the printer, but I recommend using the RecieptBuilder (see 4. Use the builder)

Simple Hello world example

```php
use LauLaman\ReceiptPrinter\Domain\Command\Action\Cut;use LauLaman\ReceiptPrinter\Domain\Command\Draw\Text;

$printer->send(
    [], //-- Printer settings more on this later 
    new Text('Hello World!'),
    new Cut(partial: true),
);
```
Let's make a receipt for a coupon for a guest Wi-Fi

```php
use LauLaman\ReceiptPrinter\Domain\Command\Action\Cut;use LauLaman\ReceiptPrinter\Domain\Command\Draw\QRCode;use LauLaman\ReceiptPrinter\Domain\Command\Draw\Separator;use LauLaman\ReceiptPrinter\Domain\Command\Draw\Text;use LauLaman\ReceiptPrinter\Domain\Command\Layout\Align;use LauLaman\ReceiptPrinter\Domain\Command\Style\Bold;use LauLaman\ReceiptPrinter\Domain\Command\Style\Font;use LauLaman\ReceiptPrinter\Domain\Command\Style\Magnify;use LauLaman\ReceiptPrinter\Domain\Command\Style\Negative;use LauLaman\ReceiptPrinter\Domain\Command\Style\Underline;use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;use LauLaman\ReceiptPrinter\Domain\Enum\FontType;use LauLaman\ReceiptPrinter\Domain\Enum\QRCodeErrorCorrection;use LauLaman\ReceiptPrinter\Domain\Receipt;

$date = (new \DateTime("+24 hours"))->format('Y-m-d H:i:s');

$receipt = new Receipt(
    [], // settings
    [
        new Align(
            Alignment::CENTER,
            new Bold(new Magnify(3, text: new Text("Wi-Fi"))), new NewLine(),
            new Magnify(2, text: new Text("YourWifi")), new NewLine(),
            new Separator(),
            new Magnify(2, text: new Negative(new Text("12345-67890"))), new NewLine(),
            new Font(FontType::B, new Underline(new Text("Valid until {$date}"))), new NewLine(),
            new Separator("."),
            new QRCode("WIFI:T:nopass;S:SSID;;", 8, QRCodeErrorCorrection::MEDIUM)
        ),
        new Cut(partial: true)
    ]
);

//-- Sent the receipt to the printer
$printer->print($receipt);
```
### 4. Use the builder
Let's make a receipt for a coupon for a guest Wi-Fi using the ReceiptBuilder

```php
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;use LauLaman\ReceiptPrinter\Domain\Enum\FontType;use LauLaman\ReceiptPrinter\Domain\ReceiptBuilder;

$date = (new \DateTime("+24 hours"))->format('Y-m-d H:i:s');

$builder = ReceiptBuilder::create()
    ->align(Alignment::CENTER) //-- Enter align mode (center)
        ->bold() //-- Enter bold mode
            ->magnify(3) //-- Enter magnification mode
                ->text("WiFi") //-- Print text
            ->end() //-- Exit magnification mode
        ->end() //-- Exit bold mode
        ->newline() //-- Start a new line
        ->magnify(2, text: "YourWifi") //-- Print text in magnification mode
        ->newline() //-- Start a new line
        ->separator() //-- Print separator
        ->magnify(2) //-- Enter magnification mode
            ->negative("12345-67890") //-- Print text in invert mode
        ->end() //-- Exit magnification mode
        ->newline() //-- Start a new line
        ->font(FontType::B) //-- Enter specific Font mode
            ->text("Valid until {$date}") //-- Print text
        ->end() //-- Exit specific Font mode
        ->newline() //-- Start a new line
        ->separator('#') //-- Print a separator and use # as char
        ->qrCode('WIFI:T:nopass;S:SSID;;', 8) //-- Print QR code
    ->end() //-- Exit align mode (center)
    ->cut(partial: true); //- Cut the paper using a partial cut;
    
$receipt = $builder->build(); // Build the receipt

//-- Sent the receipt to the printer
$printer->print($receipt);
```
---

### 3. Print a Full Store Receipt

```php
use LauLaman\ReceiptPrinter\Domain\Enum\Alignment;use LauLaman\ReceiptPrinter\Domain\Enum\BarcodeType;use LauLaman\ReceiptPrinter\Domain\ReceiptBuilder;use PrintSetting\CodePageSetting;

$builder = ReceiptBuilder::create(new CodePageSetting())
    ->align(Alignment::CENTER) //-- Enter align mode (center)
        ->logo(2) //-- Print logo stored on the memory on the printer itself
        ->bold() //-- Enter bold mode
            ->magnify(2, text: "MY STORE") //-- Print text with a 2 x magnification
        ->end() //-- Exit bold mode
        ->text("123 Main Street") //-- Print text
        ->text("City, State 12345") //-- Print text
        ->text("Tel: (555) 123-4567") //-- Print text
        ->feed() //-- Feed the paper by 1 line 
        ->separator() //-- Print a seperator
        ->magnify(3, 4)//-- Enter magnification mode, magnify 3x in with and 4x in height
            ->negative("10897") //-- Print negative text (white on black)
        ->end()//-- Exit magnification mode
    ->end() //-- Exit align mode (center)
    ->separator() //-- Print separator
    ->column("1 x Cappuccino", "€4.50") //-- Print a column with text on the left and the price on the right
    ->column("1 x Pizza", "€13.50")
    ->column("    2 x Deposit",  fn($right) => $right->negative("€0.30"))//- Print a column with the right text negative
    ->separator() //-- Print separator
    ->bold() //-- Enter bold mode
        ->column("TOTAL", "€43.30") //-- Print a column but now bold since we're in bold mode
    ->end() //-- Exit bold mode
    ->feed()//-- Feed the paper by 1 line 
    ->align(Alignment::CENTER)//-- Enter align mode (center)
        ->text("Thank you for your purchase!") //-- Print text
        ->feed(2)//-- Feed the paper by 2 lines 
        ->barcode("123456", BarcodeType::CODE128) //-- Print a code 128 barcode
    ->end()//-- Exit align mode (center)
    ->cut();//-- Cut paper

$receipt = $builder->build(); // Build the receipt

//-- Sent the receipt to the printer
$printer->print($receipt);
```
---

## Contributing

1. Fork the repository
2. Make your changes
3. Update the `composer.json` version if needed
4. Submit a pull request
