<?php

declare(strict_types=1);

namespace App\Tests;

use App\Infrastructure\Doctrine\Repository\FantasyLeagueRecordRepository;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use App\Tests\Support\DatabaseSchemaTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FantasyHomeFlowTest extends WebTestCase
{
    use DatabaseSchemaTrait;

    public function testUserCanCreateOneCommonTeamAndALeague(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();
        $this->register($client, 'manuel');

        $client->request('GET', '/');
        self::assertSelectorTextContains('body', 'Tu n’as pas encore composé ton équipe');
        self::assertSelectorTextContains('body', 'Tu ne participes encore à aucune ligue');
        self::assertSelectorTextContains('body', 'Étapes courues');
        self::assertSelectorTextContains('body', 'Temps équipe');
        self::assertSelectorTextContains('body', 'Classement petit toureur');
        self::assertSelectorTextContains('body', 'Aucune équipe');

        $client->request('GET', '/mon-equipe/creation');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[data-team-rider-search]');
        self::assertSelectorExists('select[data-team-rider-sort]');
        self::assertStringContainsString('data-team-rider-card', (string) $client->getResponse()->getContent());
        $client->request('POST', '/mon-equipe/creation', [
            'name' => 'Les Bordures',
            'riders' => $this->riderIds([
                'jonas-vingegaard',
                'bruno-armirail',
                'nils-politt',
                'nelson-oliveira',
                'luka-mezgec',
                'anthony-turgis',
                'jonas-rutsch',
                'simon-guglielmi',
            ]),
            '_token' => $this->csrfToken($client),
        ]);
        self::assertResponseRedirects('/');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Les Bordures');
        self::assertSelectorTextContains('body', 'Jonas Vingegaard');
        self::assertSelectorTextContains('body', 'leader');
        self::assertSelectorTextContains('body', '#1');

        $client->request('GET', '/ligues/creation');
        $client->submitForm('Créer la ligue', ['name' => 'Ligue du samedi']);
        self::assertResponseRedirects('/');
        $client->followRedirect();

        self::assertSelectorTextContains('body', 'Ligue du samedi');
        self::assertSelectorTextContains('body', 'Code');
        self::assertSelectorTextContains('body', 'Dashboard');
        self::assertSelectorTextContains('body', 'Les Bordures');
        self::assertStringContainsString(
            'border-l-4 border-emerald-600 bg-emerald-50/80',
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testAnotherUserCanJoinALeagueWithSharedCodeUsingTheirCommonTeam(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();
        $this->register($client, 'manuel');
        $this->createTeam($client, 'Les Bordures');
        $client->request('GET', '/ligues/creation');
        $client->submitForm('Créer la ligue', ['name' => 'Ligue partagée']);

        /** @var FantasyLeagueRecordRepository $leagues */
        $leagues = self::getContainer()->get(FantasyLeagueRecordRepository::class);
        $league = $leagues->findOneByCode($leagues->findByParticipant($this->currentUser($client))[0]->code());
        self::assertNotNull($league);
        $code = $league->code();

        $client->request('GET', '/deconnexion');
        $client->followRedirect();
        $this->register($client, 'claire');
        $this->createTeam($client, 'La Musette');

        $client->request('GET', '/ligues/rejoindre');
        $client->submitForm('Rejoindre', ['code' => $code]);
        self::assertResponseRedirects('/');
        $client->followRedirect();

        self::assertSelectorTextContains('body', 'Ligue partagée');
        self::assertSelectorTextContains('body', 'La Musette');
    }

    public function testLeagueDashboardDisplaysGeneralAndStageStandings(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();
        $this->register($client, 'manuel');
        $this->createTeam($client, 'Les Bordures');
        $client->request('GET', '/ligues/creation');
        $client->submitForm('Créer la ligue', ['name' => 'Ligue montagne']);

        /** @var FantasyLeagueRecordRepository $leagues */
        $leagues = self::getContainer()->get(FantasyLeagueRecordRepository::class);
        $league = $leagues->findByParticipant($this->currentUser($client))[0];

        $client->request('GET', sprintf('/ligues/%d', $league->id()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Ligue montagne');
        self::assertSelectorTextContains('body', 'Classement général');
        self::assertSelectorTextContains('body', 'Étape 1');
        self::assertSelectorTextContains('body', 'Florence - Rimini');
        self::assertSelectorTextContains('body', 'Voir la carte');
        self::assertSelectorTextContains('body', 'Classement users');
        self::assertSelectorTextContains('body', 'Classement riders');
        self::assertSelectorTextContains('body', 'Mes coureurs');
        self::assertSelectorTextContains('body', 'Compositions');
        self::assertSelectorTextContains('body', 'Écart');
        self::assertSelectorTextContains('body', 'Les Bordures');
        self::assertSelectorTextContains('body', 'Tadej Pogacar');
        self::assertStringContainsString('data-stage-rider-team-toggle', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('data-stage-rider-row', (string) $client->getResponse()->getContent());
        self::assertStringContainsString(
            'border-l-4 border-emerald-600 bg-emerald-50/80',
            (string) $client->getResponse()->getContent(),
        );
    }

    public function testLeagueTeamCompositionsDisplaysEveryParticipantTeam(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();
        $this->register($client, 'manuel');
        $this->createTeam($client, 'Les Bordures');
        $client->request('GET', '/ligues/creation');
        $client->submitForm('Créer la ligue', ['name' => 'Ligue compositions']);

        /** @var FantasyLeagueRecordRepository $leagues */
        $leagues = self::getContainer()->get(FantasyLeagueRecordRepository::class);
        $league = $leagues->findByParticipant($this->currentUser($client))[0];
        $code = $league->code();

        $client->request('GET', '/deconnexion');
        $client->followRedirect();
        $this->register($client, 'claire');
        $this->createTeam($client, 'La Musette');
        $client->request('GET', '/ligues/rejoindre');
        $client->submitForm('Rejoindre', ['code' => $code]);
        $client->followRedirect();

        $client->request('GET', sprintf('/ligues/%d/equipes', $league->id()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Ligue compositions');
        self::assertSelectorTextContains('body', 'Les Bordures');
        self::assertSelectorTextContains('body', 'La Musette');
        self::assertSelectorTextContains('body', 'Manager manuel');
        self::assertSelectorTextContains('body', 'Manager claire');
        self::assertSelectorTextContains('body', 'Tadej Pogacar');
        self::assertSelectorTextContains('body', 'Profil polyvalent');
        self::assertSelectorTextContains('body', 'En course');
    }

    public function testHeaderLinksToOfficialAndGlobalFantasyClassifications(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();
        $this->register($client, 'manuel');
        $this->createTeam($client, 'Les Bordures');

        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('nav', 'Classement officiel');
        self::assertSelectorTextContains('nav', 'Classement petit toureur');
        self::assertStringContainsString('data-theme-toggle', (string) $client->getResponse()->getContent());

        $client->request('GET', '/classement-general-officiel-coureurs');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Classement général des coureurs');
        self::assertSelectorTextContains('body', 'Tadej Pogacar');
        self::assertSelectorTextContains('body', 'Temps cumulé');

        $client->request('GET', '/classement-petit-toureur');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Classement petit toureur');
        self::assertSelectorTextContains('body', 'Les Bordures');
        self::assertSelectorTextContains('body', 'manuel');
        self::assertStringContainsString(
            'border-l-4 border-emerald-600 bg-emerald-50/80',
            (string) $client->getResponse()->getContent(),
        );
    }

    private function register(KernelBrowser $client, string $username): void
    {
        $client->request('GET', '/inscription');
        $client->submitForm('Créer le compte', [
            'username' => $username,
            'password' => 'secret',
        ]);
        $client->followRedirect();
    }

    private function createTeam(KernelBrowser $client, string $name): void
    {
        $client->request('GET', '/mon-equipe/creation');
        $client->request('POST', '/mon-equipe/creation', [
            'name' => $name,
            'riders' => $this->riderIds([
                'tadej-pogacar',
                'georg-zimmermann',
                'amaury-capiot',
                'jonas-rutsch',
                'kevin-geniets',
                'simon-guglielmi',
                'nelson-oliveira',
                'luka-mezgec',
            ]),
            '_token' => $this->csrfToken($client),
        ]);
        $client->followRedirect();
    }

    private function csrfToken(KernelBrowser $client): string
    {
        return (string) $client->getCrawler()->filter('input[name="_token"]')->attr('value');
    }

    /**
     * @param list<string> $slugs
     * @return list<int>
     */
    private function riderIds(array $slugs): array
    {
        /** @var RiderRecordRepository $riders */
        $riders = self::getContainer()->get(RiderRecordRepository::class);
        $ids = [];

        foreach ($slugs as $slug) {
            $rider = $riders->findOneBySlug($slug);
            self::assertNotNull($rider);
            $ids[] = $rider->id();
        }

        return $ids;
    }

    private function currentUser(KernelBrowser $client): \App\Infrastructure\Doctrine\Entity\ApplicationUser
    {
        $client->request('GET', '/');

        /** @var \App\Infrastructure\Doctrine\Entity\ApplicationUser $user */
        $user = self::getContainer()->get('security.token_storage')->getToken()?->getUser();

        return $user;
    }
}
