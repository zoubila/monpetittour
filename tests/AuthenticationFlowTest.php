<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Support\DatabaseSchemaTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthenticationFlowTest extends WebTestCase
{
    use DatabaseSchemaTrait;

    public function testHomePageRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/');

        self::assertResponseRedirects('http://localhost/connexion');
    }

    public function testUserCanRegisterAndReachHomePage(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();

        $client->request('GET', '/inscription');
        self::assertSelectorExists('button[data-theme-toggle]');

        $client->submitForm('Créer le compte', [
            'username' => 'manuel',
            'password' => 'secret',
        ]);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Ton équipe est commune à toutes tes ligues');
        self::assertSelectorTextContains('body', 'Mon équipe');
        self::assertSelectorTextContains('body', 'Mes ligues');
        self::assertSelectorExists('button[data-theme-toggle]');
    }

    public function testRegisterShowsAnErrorWhenUsernameAlreadyExists(): void
    {
        $client = self::createClient();
        $this->recreateDatabaseSchema();

        $client->request('GET', '/inscription');
        $client->submitForm('Créer le compte', [
            'username' => 'manuel',
            'password' => 'secret',
        ]);

        $client->request('GET', '/inscription');
        $client->submitForm('Créer le compte', [
            'username' => 'manuel',
            'password' => 'autre-secret',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'existe déjà');
        self::assertSelectorExists('input[value="manuel"]');
    }
}
