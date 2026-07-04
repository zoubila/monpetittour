<?php

declare(strict_types=1);

namespace App\Infrastructure\Fixture;

use App\Domain\Entity\CumulativeStanding;
use App\Domain\Entity\FantasyTeam;
use App\Domain\Entity\FantasyUser;
use App\Domain\Entity\League;
use App\Domain\Entity\Rider;
use App\Domain\Entity\Stage;
use App\Domain\Enum\RiderSpecialty;
use App\Domain\Repository\FantasyCatalogInterface;

final class FantasyFixtureCatalog implements FantasyCatalogInterface
{
    /**
     * @return list<Rider>
     */
    public function riders(): array
    {
        return [
            new Rider('tadej-pogacar', 'Tadej Pogacar', 'UAE Team Emirates', 'Slovénie', 450_000, RiderSpecialty::Leader),
            new Rider('jonas-vingegaard', 'Jonas Vingegaard', 'Visma Lease a Bike', 'Danemark', 400_000, RiderSpecialty::Leader),
            new Rider('remco-evenepoel', 'Remco Evenepoel', 'Soudal Quick-Step', 'Belgique', 320_000, RiderSpecialty::TimeTrialist),
            new Rider('primoz-roglic', 'Primoz Roglic', 'Bora Hansgrohe', 'Slovénie', 300_000, RiderSpecialty::Leader),
            new Rider('wout-van-aert', 'Wout van Aert', 'Visma Lease a Bike', 'Belgique', 220_000, RiderSpecialty::TimeTrialist),
            new Rider('mathieu-van-der-poel', 'Mathieu van der Poel', 'Alpecin Deceuninck', 'Pays-Bas', 210_000, RiderSpecialty::Sprinter),
            new Rider('jasper-philipsen', 'Jasper Philipsen', 'Alpecin Deceuninck', 'Belgique', 180_000, RiderSpecialty::Sprinter),
            new Rider('adam-yates', 'Adam Yates', 'UAE Team Emirates', 'Royaume-Uni', 140_000, RiderSpecialty::Climber),
            new Rider('joao-almeida', 'Joao Almeida', 'UAE Team Emirates', 'Portugal', 130_000, RiderSpecialty::Climber),
            new Rider('mikel-landa', 'Mikel Landa', 'Soudal Quick-Step', 'Espagne', 110_000, RiderSpecialty::Climber),
            new Rider('sepp-kuss', 'Sepp Kuss', 'Visma Lease a Bike', 'États-Unis', 95_000, RiderSpecialty::Domestique),
            new Rider('matej-mohoric', 'Matej Mohoric', 'Bahrain Victorious', 'Slovénie', 85_000, RiderSpecialty::TimeTrialist),
            new Rider('derek-gee', 'Derek Gee', 'Israel Premier Tech', 'Canada', 62_000, RiderSpecialty::Domestique),
            new Rider('quentin-pacher', 'Quentin Pacher', 'Groupama FDJ', 'France', 18_000, RiderSpecialty::Domestique),
            new Rider('bruno-armirail', 'Bruno Armirail', 'Decathlon AG2R La Mondiale', 'France', 16_000, RiderSpecialty::TimeTrialist),
            new Rider('nils-politt', 'Nils Politt', 'UAE Team Emirates', 'Allemagne', 15_000, RiderSpecialty::Domestique),
            new Rider('nelson-oliveira', 'Nelson Oliveira', 'Movistar Team', 'Portugal', 8_000, RiderSpecialty::Domestique),
            new Rider('luka-mezgec', 'Luka Mezgec', 'Jayco AlUla', 'Slovénie', 8_000, RiderSpecialty::Sprinter),
            new Rider('anthony-turgis', 'Anthony Turgis', 'TotalEnergies', 'France', 11_000, RiderSpecialty::TimeTrialist),
            new Rider('georg-zimmermann', 'Georg Zimmermann', 'Intermarché Wanty', 'Allemagne', 6_000, RiderSpecialty::Climber),
            new Rider('amaury-capiot', 'Amaury Capiot', 'Arkéa B&B Hotels', 'Belgique', 5_000, RiderSpecialty::Sprinter),
            new Rider('jonas-rutsch', 'Jonas Rutsch', 'EF Education EasyPost', 'Allemagne', 5_000, RiderSpecialty::Domestique),
            new Rider('kevin-geniets', 'Kevin Geniets', 'Groupama FDJ', 'Luxembourg', 5_000, RiderSpecialty::Domestique),
            new Rider('simon-guglielmi', 'Simon Guglielmi', 'Arkéa B&B Hotels', 'France', 5_000, null),
        ];
    }

    public function riderBySlug(string $slug): ?Rider
    {
        foreach ($this->riders() as $rider) {
            if ($rider->slug === $slug) {
                return $rider;
            }
        }

        return null;
    }

    public function mainLeague(): League
    {
        return new League('Ligue des Copains', $this->fantasyTeams());
    }

    /**
     * @return list<Stage>
     */
    public function stages(): array
    {
        return [
            new Stage(1, 'Florence - Rimini', 206),
            new Stage(2, 'Cesenatico - Bologne', 199),
            new Stage(3, 'Plaisance - Turin', 231),
            new Stage(4, 'Turin - Valloire', 178),
        ];
    }

    /**
     * @return list<CumulativeStanding>
     */
    public function standings(): array
    {
        $teams = $this->fantasyTeams();

        return [
            new CumulativeStanding($teams[0], 94_320, 1),
            new CumulativeStanding($teams[1], 95_760, 2),
        ];
    }

    /**
     * @return list<FantasyTeam>
     */
    private function fantasyTeams(): array
    {
        $riders = $this->ridersIndexedBySlug();

        return [
            new FantasyTeam(
                'Les Bordures',
                new FantasyUser('manuel'),
                [
                    $riders['jonas-vingegaard'],
                    $riders['bruno-armirail'],
                    $riders['nils-politt'],
                    $riders['nelson-oliveira'],
                    $riders['luka-mezgec'],
                    $riders['anthony-turgis'],
                    $riders['jonas-rutsch'],
                    $riders['simon-guglielmi'],
                ],
            ),
            new FantasyTeam(
                'La Musette',
                new FantasyUser('claire'),
                [
                    $riders['tadej-pogacar'],
                    $riders['georg-zimmermann'],
                    $riders['amaury-capiot'],
                    $riders['jonas-rutsch'],
                    $riders['kevin-geniets'],
                    $riders['simon-guglielmi'],
                    $riders['nelson-oliveira'],
                    $riders['luka-mezgec'],
                ],
            ),
        ];
    }

    /**
     * @return array<string, Rider>
     */
    private function ridersIndexedBySlug(): array
    {
        $indexedRiders = [];

        foreach ($this->riders() as $rider) {
            $indexedRiders[$rider->slug] = $rider;
        }

        return $indexedRiders;
    }
}
