<?php

declare(strict_types=1);

namespace App\Tests;

use App\Infrastructure\External\Letour\LetourTourDeFrance2026Parser;
use PHPUnit\Framework\TestCase;

final class LetourTourDeFrance2026ParserTest extends TestCase
{
    public function testItParsesTourDeFranceStagesFromLetourHtml(): void
    {
        $stages = (new LetourTourDeFrance2026Parser())->parseStages(<<<'HTML'
            <html>
                <body>
                    <section class="generalRace">
                        <table>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>C.l.m par équipe</td>
                                    <td>Sam. 04/07/2026</td>
                                    <td>Barcelone &gt; Barcelone</td>
                                    <td>19.6 km</td>
                                    <td>Étape 1</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Accidentée</td>
                                    <td>Dim. 05/07/2026</td>
                                    <td>Tarragone &gt; Barcelone</td>
                                    <td>168.5 km</td>
                                    <td>Étape 2</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </body>
            </html>
            HTML);

        self::assertCount(2, $stages);
        self::assertSame(1, $stages[0]->number);
        self::assertSame('Barcelone', $stages[0]->startLocation);
        self::assertSame('Barcelone', $stages[0]->finishLocation);
        self::assertSame(19.6, $stages[0]->distanceInKilometers);
        self::assertSame(0, $stages[0]->positiveElevationInMeters);
        self::assertSame('Tarragone', $stages[1]->startLocation);
        self::assertSame('Barcelone', $stages[1]->finishLocation);
        self::assertSame(168.5, $stages[1]->distanceInKilometers);
    }

    public function testItParsesStageProfileImageFromLetourStageHtml(): void
    {
        $mapPath = (new LetourTourDeFrance2026Parser())->parseStageProfilePath(<<<'HTML'
            <html>
                <body>
                    <img class="sporting__content__img" data-src="https://img.aso.fr/core_app/img-cycling-tdf-jpg/tdf26-profils-web-fr-e-tape-1/71083/0:0,1004:602-960-0-90/f71aa" alt="" />
                </body>
            </html>
            HTML);

        self::assertSame(
            'https://img.aso.fr/core_app/img-cycling-tdf-jpg/tdf26-profils-web-fr-e-tape-1/71083/0:0,1004:602-960-0-90/f71aa',
            $mapPath,
        );
    }

    public function testItParsesStageResultsFromLetourHtml(): void
    {
        $results = (new LetourTourDeFrance2026Parser())->parseStageResults(<<<'HTML'
            <html>
                <body>
                    <table class="rankingTable">
                        <thead>
                            <tr>
                                <th>Rang</th><th>Coureur</th><th>Dossard</th><th>Équipe</th><th>Temps</th>
                            </tr>
                        </thead>
                        <tr class="rankingTables__row">
                            <td>1</td><td>&nbsp; J. VINGEGAARD <a class="rankingTables__row__profile--name" href="/fr/coureur/11/team-visma-lease-a-bike/jonas-vingegaard">J. VINGEGAARD</a></td><td>11</td><td>TEAM VISMA | LEASE A BIKE</td><td>00h 21' 47''</td><td>-</td>
                        </tr>
                        <tr class="rankingTables__row">
                            <td>2</td><td>&nbsp; T. POGACAR</td><td>1</td><td>UAE TEAM EMIRATES XRG</td><td>00h 21' 59''</td><td>+ 00h 00' 12''</td>
                        </tr>
                    </table>
                </body>
            </html>
            HTML);

        self::assertCount(2, $results);
        self::assertSame(1, $results[0]->position);
        self::assertSame('jonas vingegaard', $results[0]->riderName);
        self::assertSame('TEAM VISMA  LEASE A BIKE', $results[0]->realTeam);
        self::assertSame((21 * 60) + 47, $results[0]->timeInSeconds);
        self::assertSame(0, $results[0]->gapInSeconds);
        self::assertSame('T. POGACAR', $results[1]->riderName);
        self::assertSame((21 * 60) + 59, $results[1]->timeInSeconds);
        self::assertSame(12, $results[1]->gapInSeconds);
    }

    public function testItParsesStageIndividualResultsAjaxPathFromLetourRankingPage(): void
    {
        $path = (new LetourTourDeFrance2026Parser())->parseStageIndividualResultsPath(<<<'HTML'
            <html>
                <body>
                    <span class="js-tabs-ranking" data-ajax-stack = {&quot;itg&quot;:&quot;\/fr\/ajax\/ranking\/2\/itg\/general\/none&quot;}>Classement général</span>
                    <span class="js-tabs-ranking" data-ajax-stack = {&quot;ite&quot;:&quot;\/fr\/ajax\/ranking\/2\/ite\/stage\/none&quot;}>Classement de l&#039;étape</span>
                </body>
            </html>
            HTML);

        self::assertSame('https://www.letour.fr/fr/ajax/ranking/2/ite/stage/none', $path);
    }

    public function testItDoesNotParseTeamStageRankingAsRiderResults(): void
    {
        $results = (new LetourTourDeFrance2026Parser())->parseStageResults(<<<'HTML'
            <html>
                <body>
                    <table class="rankingTable">
                        <thead>
                            <tr>
                                <th>Rang</th><th>Équipe</th><th>Temps</th><th>Écart</th>
                            </tr>
                        </thead>
                        <tr class="rankingTables__row">
                            <td>1</td><td>TEAM VISMA | LEASE A BIKE</td><td>00h 21' 47''</td><td>-</td>
                        </tr>
                    </table>
                </body>
            </html>
            HTML);

        self::assertSame([], $results);
    }
}
