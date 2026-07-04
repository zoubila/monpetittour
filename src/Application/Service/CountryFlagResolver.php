<?php

declare(strict_types=1);

namespace App\Application\Service;

final class CountryFlagResolver
{
    /**
     * @var array<string, string>
     */
    private const COUNTRY_CODES = [
        'allemagne' => 'DE',
        'australia' => 'AU',
        'austria' => 'AT',
        'belgique' => 'BE',
        'belgium' => 'BE',
        'canada' => 'CA',
        'colombia' => 'CO',
        'czech republic' => 'CZ',
        'czechia' => 'CZ',
        'danemark' => 'DK',
        'denmark' => 'DK',
        'ecuador' => 'EC',
        'eritrea' => 'ER',
        'espagne' => 'ES',
        'etats unis' => 'US',
        'france' => 'FR',
        'germany' => 'DE',
        'great britain' => 'GB',
        'ireland' => 'IE',
        'italy' => 'IT',
        'kazakhstan' => 'KZ',
        'latvia' => 'LV',
        'luxembourg' => 'LU',
        'mexico' => 'MX',
        'netherlands' => 'NL',
        'new zealand' => 'NZ',
        'norway' => 'NO',
        'pays bas' => 'NL',
        'poland' => 'PL',
        'portugal' => 'PT',
        'royaume uni' => 'GB',
        'slovenia' => 'SI',
        'slovenie' => 'SI',
        'spain' => 'ES',
        'switzerland' => 'CH',
        'united kingdom' => 'GB',
        'united states' => 'US',
        'usa' => 'US',
    ];

    public function forNationality(string $nationality): string
    {
        $normalizedNationality = $this->normalize($nationality);
        $countryCode = self::COUNTRY_CODES[$normalizedNationality] ?? null;

        if ($countryCode === null && preg_match('/^[a-z]{2}$/', $normalizedNationality) === 1) {
            $countryCode = strtoupper($normalizedNationality);
        }

        if ($countryCode === null) {
            return '';
        }

        return $this->flagFromCountryCode($countryCode);
    }

    private function normalize(string $nationality): string
    {
        $nationality = strtr(trim($nationality), [
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ä' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Î' => 'I',
            'Ï' => 'I',
            'Ô' => 'O',
            'Ö' => 'O',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'î' => 'i',
            'ï' => 'i',
            'ô' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
        ]);

        $asciiNationality = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nationality);

        if ($asciiNationality === false) {
            $asciiNationality = $nationality;
        }

        $normalizedNationality = strtolower($asciiNationality);
        $normalizedNationality = str_replace(['\'', '`'], '', $normalizedNationality);
        $normalizedNationality = str_replace(['.', '-'], ' ', $normalizedNationality);
        $normalizedNationality = preg_replace('/\s+/', ' ', $normalizedNationality) ?? $normalizedNationality;

        return trim($normalizedNationality);
    }

    private function flagFromCountryCode(string $countryCode): string
    {
        $countryCode = strtoupper($countryCode);
        $firstRegionalIndicator = 127_462;

        return html_entity_decode(
            sprintf(
                '&#%d;&#%d;',
                $firstRegionalIndicator + ord($countryCode[0]) - ord('A'),
                $firstRegionalIndicator + ord($countryCode[1]) - ord('A'),
            ),
            ENT_NOQUOTES,
            'UTF-8',
        );
    }
}
