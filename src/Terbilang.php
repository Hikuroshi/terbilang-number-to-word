<?php

namespace Hikuroshi\Terbilang;

class Terbilang {
    protected int $number;
    protected string $separator = ' ';
    protected bool $apart = false;
    protected bool $simply = false;
    protected array $simplifyRules = [];
    protected string $caseStyle = 'default';
    protected array $languageRules;
    protected string $language = 'en';

    public function __construct() {
        $this->loadLanguageRules();
    }

    /**
     * Create a new instance of the Terbilang class with the specified number.
     *
     * @param int  $number The number to be converted to words.
     * @param bool $apart  Whether to use separated word representation for each digit (true) or full number word representation (false).
     * @return self        Returns an instance of the Terbilang class.
     */
    public static function terbilang(int $number, bool $apart = false): self {
        $instance = new self();
        $instance->number = $number;
        $instance->apart = $apart;
        return $instance;
    }

    /**
     * Set the language for number conversion.
     *
     * @param string $language The language code (e.g., 'id' for Indonesian, 'en' for English).
     * @return self            Returns the current instance of the Terbilang class.
     */
    public function lang(string $language): self {
        $this->language = $language;
        $this->loadLanguageRules();
        return $this;
    }

    /**
     * Simplify the word representation based on the specified rules.
     *
     * @param array|bool $simply If true, apply default simplification rules. If array, use the specified simplification rules.
     * @return self              Returns the current instance of the Terbilang class.
     */
    public function simply($simply = true): self {
        if (is_array($simply)) {
            $this->simplifyRules = $simply;
            $this->simply = true;
        } else {
            $this->simply = $simply;
        }
        return $this;
    }

    /**
     * Set the separator to use between words.
     *
     * @param string $separator The separator string.
     * @return self             Returns the current instance of the Terbilang class.
     */
    public function separator(string $separator): self {
        $this->separator = $separator;
        return $this;
    }

    /**
     * Set the case style for the output string.
     *
     * @param string $caseStyle The case style 'camel', 'snake', 'kebab', 'pascal', 'macro', or 'train'.
     * @return self             Returns the current instance of the Terbilang class.
     */
    public function caseStyle(string $caseStyle): self {
        $this->caseStyle = $caseStyle;
        return $this;
    }

    public function __toString(): string {
        $terbilang = $this->apart ? $this->convertNumberToWordsApart($this->number) : $this->convertNumberToWords($this->number);

        $terbilang = implode(' ', $terbilang);
        $terbilang = str_replace(' ', $this->separator, $terbilang);

        return $this->applyCaseStyle($terbilang);
    }

    protected function loadLanguageRules() {
        $filePath = __DIR__ . '/lang/' . $this->language . '.json';
        if (file_exists($filePath)) {
            $this->languageRules = json_decode(file_get_contents($filePath), true);
            $this->simplifyRules = $this->languageRules['simplifyRules'] ?? [];
        } else {
            throw new \Exception("Language file not found: " . $filePath);
        }
    }

    protected function convertNumberToWords(int $number): array {
        $thousands = $this->languageRules['thousands'];

        if ($number == 0) {
            return [$this->languageRules['units'][0]];
        }

        $terbilang = [];
        $index = 0;

        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk > 0) {
                $terbilangChunk = $this->convertChunkToWords($chunk);
                if ($index > 0) {
                    $terbilangChunk[] = $thousands[$index];
                }
                $terbilang = array_merge($terbilangChunk, $terbilang);
            }
            $number = intval($number / 1000);
            $index++;
        }

        return $this->simply ? $this->simplify($terbilang) : $terbilang;
    }

    protected function convertChunkToWords(int $number): array {
        $units = $this->languageRules['units'];
        $teens = $this->languageRules['teens'];
        $tens = $this->languageRules['tens'];
        $hundred = $this->languageRules['hundred'];

        $terbilang = [];

        if ($number >= 100) {
            $terbilang[] = $units[intval($number / 100)];
            $terbilang[] = $hundred;
            $number %= 100;
        }

        if ($number >= 20) {
            $terbilang[] = $tens[intval($number / 10)];
            $number %= 10;
        } elseif ($number >= 10) {
            $terbilang[] = $teens[$number - 10];
            $number = 0;
        }

        if ($number > 0) {
            $terbilang[] = $units[$number];
        }

        return $terbilang;
    }

    protected function convertNumberToWordsApart(int $number): array {
        $units = $this->languageRules['units'];
        return array_map(fn($digit) => $units[$digit], str_split((string)$number));
    }

    protected function simplify(array $words): array {
        $simplified = [];
        $length = count($words);

        for ($i = 0; $i < $length; $i++) {
            if ($words[$i] === $this->languageRules['units'][1] && $i + 1 < $length && in_array($words[$i + 1], $this->simplifyRules)) {
                $simplified[] = $this->languageRules['simply'] . $words[$i + 1];
                $i++;
            } else {
                $simplified[] = $words[$i];
            }
        }

        return $simplified;
    }

    protected function applyCaseStyle(string $text): string {
        switch ($this->caseStyle) {
            case 'camel':
                return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $text))));
            case 'snake':
                return strtolower(str_replace(' ', '_', $text));
            case 'kebab':
                return strtolower(str_replace(' ', '-', $text));
            case 'pascal':
                return str_replace(' ', '', ucwords(str_replace('_', ' ', $text)));
            case 'macro':
                return strtoupper(str_replace(' ', '_', $text));
            case 'train':
                return ucwords(str_replace(' ', '-', $text));
            default:
                return $text;
        }
    }
}