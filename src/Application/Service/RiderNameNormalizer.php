<?php

declare(strict_types=1);

namespace App\Application\Service;

final class RiderNameNormalizer
{
    /**
     * @return list<string>
     */
    public function comparableNames(string $name): array
    {
        $normalizedName = $this->normalize($name);
        $names = [$normalizedName];
        $parts = explode(' ', $normalizedName);

        if (count($parts) > 1) {
            $names[] = implode(' ', array_reverse($parts));
        }

        return array_values(array_unique(array_filter($names)));
    }

    public function normalize(string $name): string
    {
        $name = strtr(trim($name), [
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ä' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ó' => 'O',
            'Ô' => 'O',
            'Ö' => 'O',
            'Ú' => 'U',
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
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ó' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
        ]);

        $asciiName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

        if ($asciiName === false) {
            $asciiName = $name;
        }

        $normalizedName = strtolower($asciiName);
        $normalizedName = preg_replace('/[^a-z ]+/', ' ', $normalizedName) ?? $normalizedName;
        $normalizedName = preg_replace('/\s+/', ' ', $normalizedName) ?? $normalizedName;

        return trim($normalizedName);
    }

    public function abbreviatedNameMatches(string $abbreviatedName, string $fullName): bool
    {
        $abbreviatedName = $this->normalize($abbreviatedName);
        $fullName = $this->normalize($fullName);

        if (preg_match('/^(?<initial>[a-z]) (?<surname>.+)$/', $abbreviatedName, $matches) !== 1) {
            return false;
        }

        $fullNameParts = explode(' ', $fullName);
        if (count($fullNameParts) < 2) {
            return false;
        }

        $firstName = array_shift($fullNameParts);
        $surname = implode(' ', $fullNameParts);

        return $matches['initial'] === $firstName[0] && $matches['surname'] === $surname;
    }

    public function namesAreCompatible(string $importedName, string $knownName): bool
    {
        $importedName = $this->normalize($importedName);
        $knownName = $this->normalize($knownName);

        if ($importedName === $knownName || str_starts_with($importedName, $knownName)) {
            return true;
        }

        $importedParts = explode(' ', $importedName);
        $knownParts = explode(' ', $knownName);

        if (count($importedParts) < 2 || count($knownParts) < 2) {
            return false;
        }

        if ($importedParts[0] !== $knownParts[0]) {
            return false;
        }

        $importedSurnameParts = array_slice($importedParts, 1);
        $knownSurnameParts = array_slice($knownParts, 1);
        $importedSurname = implode('', $importedSurnameParts);
        $knownSurname = implode('', $knownSurnameParts);

        if (
            str_ends_with($importedSurname, $knownSurname)
            || str_starts_with($importedSurname, $knownSurname)
            || str_starts_with($knownSurname, $importedSurname)
        ) {
            return true;
        }

        foreach ($importedSurnameParts as $importedSurnamePart) {
            foreach ($knownSurnameParts as $knownSurnamePart) {
                if (strlen($importedSurnamePart) >= 3 && $importedSurnamePart === $knownSurnamePart) {
                    return true;
                }

                if (strlen($importedSurnamePart) >= 6 && levenshtein($importedSurnamePart, $knownSurnamePart) <= 1) {
                    return true;
                }
            }
        }

        return false;
    }
}
