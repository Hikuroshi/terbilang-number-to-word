# Terbilang Number To Word

Terbilang is a PHP library for converting numbers into words in any language. It provides a simple and flexible way to convert numeric values into their word representations.

## Installation

You can install this package via Composer:

```bash
composer require hikuroshi/terbilang-number-to-word
```

## Usage

```php
use Hikuroshi\Terbilang;

// Convert number to words
echo Terbilang::terbilang(24434); // Outputs "twenty four thousand four hundred thirty four"

// Convert number to words with digit separation
echo Terbilang::terbilang(24434, true); // Outputs "two four four three four"
```

## Methods

### `Terbilang::terbilang(int $number, bool $apart = false): Terbilang`

Create a new instance of the Terbilang class with the specified number.

- `$number`: The number to be converted to words.
- `$apart`: Whether to use separated word representation for each digit (true) or full number word representation (false).

```php
use Hikuroshi\Terbilang;

// Convert number to words
echo Terbilang::terbilang(24434); // Outputs "twenty four thousand four hundred thirty four"
```

### `Terbilang::lang(string $language): Terbilang`

Set the language for number conversion.

- `$language`: The language code (e.g., 'id' for Indonesian, 'en' for English).

```php
use Hikuroshi\Terbilang;

// Convert number to words with Indonesian language
echo Terbilang::terbilang(24434)->lang('id'); // Outputs "dua puluh empat ribu empat ratus tiga puluh empat"
```

### `Terbilang::simply($simply = true): Terbilang`

Simplify the word representation based on the specified rules.

- `$simply`: If true, apply default simplification rules. If array, use the specified simplification rules.

```php
use Hikuroshi\Terbilang;

// Convert number with default simplification
echo Terbilang::terbilang(1111)->simply(); // Outputs "a thousand a hundred eleven"

// Convert number with specified simplification
echo Terbilang::terbilang(1111)->simply(['hundred']); // Outputs "one thousand a hundred eleven"
```

### `Terbilang::separator(string $separator): Terbilang`

Set the separator to use between words.

- `$separator`: The separator string.

```php
use Hikuroshi\Terbilang;

// Convert number with custom separator
echo Terbilang::terbilang(24434)->separator(' >//< '); // Outputs "twenty >//< four >//< thousand >//< four >//< hundred >//< thirty >//< four"
```

### `Terbilang::caseStyle(string $caseStyle): Terbilang`

Set the case style for the output string.

- `$caseStyle`: The case style 'camel', 'snake', 'kebab', 'pascal', 'macro', or 'train'.

```php
use Hikuroshi\Terbilang;

// Convert number with camel case style
echo Terbilang::terbilang(24434)->caseStyle('camel'); // Outputs "twentyFourThousandFourHundredThirtyFour"
```

## Contributing

Thank you for considering contributing to this project!

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support the Project

If you find this project useful and would like to support its development, please consider making a donation. Your contributions help to cover the costs of development and ensure the project remains well-maintained.

[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/hikuroshi)

Thank you for your support!
