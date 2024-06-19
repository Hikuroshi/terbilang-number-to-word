<?php

namespace Hikuroshi\Terbilang;

class Terbilang
{
    protected int $number = 0;
    protected bool $apart = false;
    protected string $separator = ' ';
    protected bool $simply = false;
    protected array $simplyRules = [];
    protected string $numberingSystem = 'default';
    protected string $language = 'en';
    protected array $languageRules = [];
    protected string $caseStyle = 'default';
    protected static bool $languageCustom = false;
    protected static array $languageRulesCustom = [];

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
        if (!is_int($number)) {
            throw new \InvalidArgumentException("Input must be an integer.");
        }

        $instance = new self();
        if (self::$languageCustom) {
            $instance->languageRules = self::$languageRulesCustom;
            $instance->simplyRules = self::$languageRulesCustom['magnitudes'];

            if (isset(self::$languageRulesCustom['numberingSystem'])) {
                $instance->numberingSystem = self::$languageRulesCustom['numberingSystem'];
            }

            self::$languageCustom = false;
        }
        $instance->number = $number;
        $instance->apart = $apart;
        return $instance;
    }

    /**
     * Load custom language rules for number conversion.
     *
     * This method allows loading custom language rules either from an array or a JSON file. Custom language rules will merge with the default language rules.
     * If an array is provided, it will merge with the default language rules.
     * If a string is provided, it will treat it as a path to a JSON file and load the rules from the file.
     *
     * @param array|string $languageData The custom language data. It can be an associative array of language rules,
     *                                   or a path to a JSON file containing the language rules.
     * @return self                      Returns an instance of the Terbilang class with the custom language rules loaded.
     *
     * @throws \Exception                Throws an exception if the language file is not found.
     * @throws \InvalidArgumentException Throws an exception if the provided language data is neither an array nor a string.
     */
    public static function loadLang($languageData): self {
        $instance = new self();
        self::$languageCustom = true;

        if (is_array($languageData)) {
            self::$languageRulesCustom = array_replace_recursive($instance->languageRules, $languageData);
        } elseif (is_string($languageData)) {
            if (file_exists($languageData)) {
                self::$languageRulesCustom = array_replace_recursive($instance->languageRules, json_decode(file_get_contents($languageData), true));
            } else {
                throw new \Exception("Please provide a valid file path. Language file not found: ". $languageData);
            }
        } else {
            throw new \InvalidArgumentException("Invalid language data provided. Must be an array or a file path.");
        }

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
            $this->simplyRules = $simply;
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

    protected function convertNumberToWords(int $number): array {
        if ($this->numberingSystem === 'japanese') {
            return $this->convertNumberToWordsJapanese($number);
        }
        return $this->convertNumberToWordsDefault($number);
    }

    protected function convertNumberToWordsDefault(int $number): array {
        $magnitudes = array_slice($this->languageRules['magnitudes'], 1);

        if ($number == 0) {
            return [$this->languageRules['units'][0]];
        }

        $terbilang = [];
        $index = 0;

        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk > 0) {
                $terbilangChunk = $this->convertChunkToWordsDefault($chunk);
                if ($index > 0) {
                    $terbilangChunk[] = $magnitudes[$index - 1];
                }
                $terbilang = array_merge($terbilangChunk, $terbilang);
            }
            $number = intval($number / 1000);
            $index++;
        }

        return $this->simply ? $this->simplify($terbilang) : $terbilang;
    }

    protected function convertChunkToWordsDefault(int $number): array {
        $units = $this->languageRules['units'];
        $teens = $this->languageRules['teens'];
        $tens = $this->languageRules['tens'];
        $hundred = $this->languageRules['magnitudes'][0];

        $terbilang = [];

        if ($number >= 100) {
            $terbilang[] = $units[intval($number / 100)];
            $terbilang[] = $hundred;
            $number %= 100;
        }

        if ($number >= 10 && $number < 20) {
            $terbilang[] = $teens[$number - 10];
            $number = 0;
        }

        if ($number >= 20) {
            $terbilang[] = $tens[intval($number / 10) - 1];
            $number %= 10;
        }

        if ($number > 0) {
            $terbilang[] = $units[$number];
        }

        return $terbilang;
    }

    protected function convertNumberToWordsJapanese(int $number): array {
        $units = $this->languageRules['units'];
        $magnitudes = array_slice($this->languageRules['magnitudes'], 0, 4);

        if ($number == 0) {
            return [$units[0]];
        }

        $terbilang = [];
        $magnitudesVal = [];
        foreach ($magnitudes as $index => $label) {
            $magnitudesVal[10000 ** ($index + 1)] = $label;
        }

        krsort($magnitudesVal);

        foreach ($magnitudesVal as $value => $label) {
            if ($number >= $value) {
                $multiplier = floor($number / $value);
                $terbilangChunk = $this->convertNumberToWordsJapanese($multiplier);
                if (!empty($terbilangChunk)) {
                    $terbilang = array_merge($terbilang, $terbilangChunk);
                }
                $terbilang[] = $label;
                $number %= $value;
            }
        }

        $terbilang = array_merge($terbilang, $this->convertChunkToWordsJapanese($number));
        return $this->simply ? $this->simplify($terbilang) : $terbilang;
    }

    protected function convertChunkToWordsJapanese(int $number): array {
        $units = $this->languageRules['units'];
        $teens = $this->languageRules['teens'];
        $hundreds = $this->languageRules['hundreds'];
        $thousands = $this->languageRules['thousands'];

        $terbilang = [];

        if ($number >= 1000) {
            $terbilang[] = $thousands[floor($number / 1000) - 1];
            $number %= 1000;
        }

        if ($number >= 100) {
            $terbilang[] = $hundreds[floor($number / 100) - 1];
            $number %= 100;
        }

        if ($number >= 10) {
            $terbilang[] = $teens[floor($number / 10) - 1];
            $number %= 10;
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

    protected function loadLanguageRules() {
        $filePath = __DIR__ . '/lang/' . $this->language . '.json';

        if (file_exists($filePath)) {
            $languageData = json_decode(file_get_contents($filePath), true);
            $this->languageRules = $languageData;
            $this->simplyRules = $this->languageRules['magnitudes'];

            if (isset($languageData['numberingSystem'])) {
                $this->numberingSystem = $languageData['numberingSystem'];
            }
        } else {
            throw new \Exception("Language file not found: " . $filePath);
        }
    }

    protected function simplify(array $words): array {
        $simplified = [];
        $length = count($words);

        for ($i = 0; $i < $length; $i++) {
            if ($words[$i] === $this->languageRules['units'][1] && $i + 1 < $length && in_array($words[$i + 1], $this->simplyRules)) {
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