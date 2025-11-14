<?php
// config/texts/loader.php
declare(strict_types=1);

/**
 * Načte překlady pro zvolený jazyk s fallback na češtinu
 *
 * @package App\Core
 * @author KRS3
 * @version 1.0
 */
class TextLoader
{
    /**
     * @var array Cache načtených překladů
     */
    private static array $loadedTexts = [];

    /**
     * Načte překlady pro daný jazyk
     *
     * @param string $language Kód jazyka (cs, en, de)
     * @return array Pole překladů
     * @throws RuntimeException Pokud soubor neexistuje
     */
    public static function load(string $language): array
    {
        // Použijeme cache pro optimalizaci
        if (isset(self::$loadedTexts[$language])) {
            return self::$loadedTexts[$language];
        }

        $filePath = __DIR__ . "/{$language}.php";

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Překladový soubor pro jazyk '{$language}' neexistuje: {$filePath}");
        }

        $texts = require $filePath;
        self::$loadedTexts[$language] = $texts;

        return $texts;
    }

    /**
     * Načte překlady s fallback na češtinu
     *
     * @param string $language Požadovaný jazyk
     * @return array Pole překladů
     */
    public static function loadWithFallback(string $language): array
    {
        $mainTexts = self::load($language);

        // Pokud není čeština, načteme fallback
        if ($language !== 'cs') {
            try {
                $fallbackTexts = self::load('cs');
                $mainTexts = self::mergeWithFallback($mainTexts, $fallbackTexts);
            } catch (\RuntimeException $e) {
                // Čeština neexistuje, použijeme pouze hlavní jazyk
            }
        }

        return $mainTexts;
    }

    /**
     * Sloučí překlady s fallback hodnotami
     *
     * @param array $main Hlavní překlady
     * @param array $fallback Fallback překlady
     * @return array Sloučené překlady
     */
    private static function mergeWithFallback(array $main, array $fallback): array
    {
        $result = $fallback; // Začneme fallbackem

        // Rekurzivně přepíšeme hodnoty z hlavního jazyka
        foreach ($main as $key => $value) {
            if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                $result[$key] = self::mergeWithFallback($value, $result[$key]);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}