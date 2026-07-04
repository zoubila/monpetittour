<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\Service\CountryFlagResolver;
use PHPUnit\Framework\TestCase;

final class CountryFlagResolverTest extends TestCase
{
    public function testItResolvesCountryFlagsFromFrenchAndEnglishNationalities(): void
    {
        $resolver = new CountryFlagResolver();

        self::assertSame($this->flag('FR'), $resolver->forNationality('France'));
        self::assertSame($this->flag('SI'), $resolver->forNationality('Slovenia'));
        self::assertSame($this->flag('SI'), $resolver->forNationality('Slovénie'));
        self::assertSame($this->flag('US'), $resolver->forNationality('États-Unis'));
        self::assertSame($this->flag('GB'), $resolver->forNationality('Great Britain'));
    }

    public function testItReturnsAnEmptyFlagForUnknownNationalities(): void
    {
        self::assertSame('', (new CountryFlagResolver())->forNationality('Unknown'));
    }

    private function flag(string $countryCode): string
    {
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
