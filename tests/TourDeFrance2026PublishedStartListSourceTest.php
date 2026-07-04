<?php

declare(strict_types=1);

namespace App\Tests;

use App\Infrastructure\External\TourDeFrance\TourDeFrance2026PublishedStartListSource;
use PHPUnit\Framework\TestCase;

final class TourDeFrance2026PublishedStartListSourceTest extends TestCase
{
    public function testItContainsTheFullPublishedTourDeFrance2026StartList(): void
    {
        $riders = (new TourDeFrance2026PublishedStartListSource())->riders();

        self::assertCount(184, $riders);
        self::assertSame('tadej-pogacar', $riders[152]->slug);
        self::assertSame('Tadej Pogacar', $riders[152]->name);
        self::assertSame('UAE Team Emirates-XRG', $riders[152]->realTeam);
        self::assertSame(0, $riders[152]->marketValueInEuros);
        self::assertNull($riders[152]->specialty);
        self::assertContains('jonas-vingegaard', array_map(static fn ($rider): string => $rider->slug, $riders));
    }
}
