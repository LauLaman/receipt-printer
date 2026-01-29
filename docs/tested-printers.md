# Tested Receipt Printers

Table of contents
#### Manufacturer
- [Star Micronics](#Star Micronics)

## Star Micronics

### Star mC-Print2
##### Paper With
 - ✅ Small
 - ❌ Large: Large paper phisically does not fit in printer

##### Connection
- ✅ TCP (Socket) 
  ```php
  # Example
  $printer = $this->printerFactory->socket(
      PrinterModel::STAR_MC_PRINT2,
      PaperWidth::SMALL,
      '1992.168.0.111'
  );
  ```
- ❓ Bluetooth  
- ❓ USB

### Star TSP650
##### Paper With
- ✅ Small
- ✅ Large

##### Connection
- ✅ Bluetooth (through usb socket)
  ```php
  # Example (tested on linux)
  $printer = $this->printerFactory->usb(
      PrinterModel::STAR_MC_PRINT2,
      PaperWidth::SMALL,
      '/dev/rfcomm0',
  );
  ```
- ❓ TCP
- ❓ USB

 
## Supported Commands by Printer
### Barcodes

| Printer        | Barcode UPC_E | Barcode UPC_A | Barcode EAN8 | Barcode EAN13 | Barcode CODE39 | Barcode ITF | Barcode CODE128 | Barcode CODE93 | GS1DataBarExpanded | Pdf417 | Qr code |
|----------------|---------------|---------------|--------------|---------------|----------------|-------------|-----------------|----------------|--------------------|--------|---------|
| Star mC-Print2 | ✅             | ✅             | ✅            | ✅             | ✅              | ✅           | ✅               | ✅              | ✅                  | ✅      | ✅       |
| Star mC-Print3 | ✅             | ✅             | ✅            | ✅             | ✅              | ✅           | ✅               | ✅              | ✅                  | ✅      | ✅       |
| Star TSP650    | ✅             | ✅             | ✅            | ✅             | ✅              | ✅           | ✅               | ✅              | ✅                  | ✅      | ✅       |

### Graphics

> [!Note]
> Image is not implemented. I am unable to figure out how to compile images to bytecode that the printer understands.

| Printer        | Logo  | Image              |
|----------------|-------|--------------------|
| Star mC-Print2 | ✅     | ❌                   |
| Star mC-Print3 | ✅     | ❌                   |
| Star TSP650    | ✅     | ❌                   |

### Layout

| Printer        | Align | Bold | Column | Font | Invert | Magnify | Margin | Separator | Text | Underline | Upperline | UpsideDown |
|----------------|-------|------|--------|------|--------|---------|--------|-----------|------|-----------|-----------|------------|
| Star mC-Print2 | ✅     | ✅    | ✅      | ✅    | ✅      | ✅       | ✅      | ✅         | ✅    | ✅         | ✅         | ✅          |
| Star mC-Print3 | ✅     | ✅    | ✅      | ✅    | ✅      | ✅       | ✅      | ✅         | ✅    | ✅         | ✅         | ✅          |
| Star TSP650    | ✅     | ✅    | ✅      | ✅    | ✅      | ✅       | ✅      | ✅         | ✅    | ✅         | ✅         | ✅          |

### Paper

| Printer        | Cut | Partial cut | Feed | OpenDrawer | PrintDensity | PrintSpeed |
|----------------|-----|-------------|------|------------|--------------|------------|
| Star mC-Print2 | ✅   | ✅           | ✅    | ❓          | ❓            | ❓          | 
| Star mC-Print3 | ✅   | ✅           | ✅    | ❓          | ❓            | ❓          |
| Star TSP650    | ✅   | ✅           | ✅    | ❓          | ❓            | ❓          |


### Sound

> [!Note]
> to play sound, an external buzzer might be required.

| Printer        | Buzzer | MelodySpeaker |
|----------------|--------|---------------|
| Star mC-Print2 | ❓      | ❓             |
| Star mC-Print3 | ❓      | ❓             |
| Star TSP650    | ❓      | ❓             |