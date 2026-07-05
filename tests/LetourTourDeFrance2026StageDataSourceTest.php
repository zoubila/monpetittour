<?php

declare(strict_types=1);

namespace App\Tests;

use App\Infrastructure\External\Letour\LetourScrapingUnavailable;
use App\Infrastructure\External\Letour\LetourTourDeFrance2026Parser;
use App\Infrastructure\External\Letour\LetourTourDeFrance2026StageDataSource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class LetourTourDeFrance2026StageDataSourceTest extends TestCase
{
    public function testStageOneUsesTheMainRankingPageBecauseItIsATeamTimeTrial(): void
    {
        $requestedUrls = [];
        $httpClient = new MockHttpClient(static function (string $method, string $url) use (&$requestedUrls): MockResponse {
            $requestedUrls[] = $url;

            return new MockResponse(self::riderRankingHtml('J. VINGEGAARD', '00h 21\' 47\'\''));
        });

        $results = (new LetourTourDeFrance2026StageDataSource(
            $httpClient,
            new LetourTourDeFrance2026Parser(),
        ))->stageResults(1);

        self::assertCount(1, $results);
        self::assertSame('J. VINGEGAARD', $results[0]->riderName);
        self::assertSame(['https://www.letour.fr/fr/classements/etape-1'], $requestedUrls);
    }

    public function testStageTwoUsesTheIndividualStageRankingAjaxEndpoint(): void
    {
        $requestedUrls = [];
        $httpClient = new MockHttpClient(static function (string $method, string $url) use (&$requestedUrls): MockResponse {
            $requestedUrls[] = $url;

            if ($url === 'https://www.letour.fr/fr/classements/etape-2') {
                return new MockResponse(<<<'HTML'
                    <span class="js-tabs-ranking" data-ajax-stack = {&quot;itg&quot;:&quot;\/fr\/ajax\/ranking\/2\/itg\/general\/none&quot;}>Classement général</span>
                    <span class="js-tabs-ranking" data-ajax-stack = {&quot;ite&quot;:&quot;\/fr\/ajax\/ranking\/2\/ite\/stage\/none&quot;}>Classement de l&#039;étape</span>
                    HTML);
            }

            return new MockResponse(self::riderRankingHtml('T. POGACAR', '04h 12\' 03\'\''));
        });

        $results = (new LetourTourDeFrance2026StageDataSource(
            $httpClient,
            new LetourTourDeFrance2026Parser(),
        ))->stageResults(2);

        self::assertCount(1, $results);
        self::assertSame('T. POGACAR', $results[0]->riderName);
        self::assertSame(
            [
                'https://www.letour.fr/fr/classements/etape-2',
                'https://www.letour.fr/fr/ajax/ranking/2/ite/stage/none',
            ],
            $requestedUrls,
        );
    }

    public function testStageTwoDoesNotFallbackToGeneralRankingWhenIndividualStageRankingIsMissing(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(self::riderRankingHtml('J. VINGEGAARD', '07h 02\' 10\'\'')),
        ]);

        $this->expectException(LetourScrapingUnavailable::class);
        $this->expectExceptionMessage('Letour individual stage ranking was not found for stage 2.');

        (new LetourTourDeFrance2026StageDataSource(
            $httpClient,
            new LetourTourDeFrance2026Parser(),
        ))->stageResults(2);
    }

    private static function riderRankingHtml(string $riderName, string $time): string
    {
        return <<<HTML
            <table class="rankingTable">
                <thead>
                    <tr>
                        <th>Rang</th><th>Coureur</th><th>Dossard</th><th>Équipe</th><th>Temps</th>
                    </tr>
                </thead>
                <tr class="rankingTables__row">
                    <td>1</td><td>{$riderName}</td><td>11</td><td>TEAM VISMA | LEASE A BIKE</td><td>{$time}</td><td>-</td>
                </tr>
            </table>
            HTML;
    }
}
