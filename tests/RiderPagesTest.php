<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Support\DatabaseSchemaTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RiderPagesTest extends WebTestCase
{
    use DatabaseSchemaTrait;

    public function testRiderListDisplaysFixtureRiders(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();
        $this->register($client);

        $client->request('GET', '/coureurs');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Coureurs disponibles');
        self::assertSelectorTextContains('body', 'Tadej Pogacar');
        self::assertSelectorTextContains('body', 'Visma Lease a Bike');
        self::assertSelectorTextContains('body', '450 000 €');
        self::assertSelectorTextContains('body', 'Total');
        self::assertSelectorTextContains('body', 'Mes coureurs');
        self::assertSelectorTextContains('body', 'Trier par');
        self::assertSelectorExists('input[data-rider-search]');
        self::assertSelectorExists('select[data-rider-sort]');
        self::assertStringContainsString('data-current-user-riders-toggle', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('data-rider-card', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('data-rider-search-value', (string) $client->getResponse()->getContent());
    }

    public function testRiderDetailsDisplaysFixtureRider(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();
        $this->register($client);

        $client->request('GET', '/coureurs/tadej-pogacar');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tadej Pogacar');
        self::assertSelectorTextContains('body', 'UAE Team Emirates');
        self::assertSelectorTextContains('body', 'leader');
        self::assertSelectorTextContains('body', 'Temps cumulé');
        self::assertSelectorTextContains('body', 'Écart cumulé');
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\KernelBrowser $client
     */
    private function register(object $client): void
    {
        $client->request('GET', '/inscription');
        $client->submitForm('Créer le compte', [
            'username' => 'tester',
            'password' => 'secret',
        ]);
        $client->followRedirect();
    }
}
