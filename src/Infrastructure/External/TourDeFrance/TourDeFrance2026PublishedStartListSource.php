<?php

declare(strict_types=1);

namespace App\Infrastructure\External\TourDeFrance;

use App\Application\DTO\ImportedRider;
use App\Application\Port\RiderImportSourceInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class TourDeFrance2026PublishedStartListSource implements RiderImportSourceInterface
{
    private AsciiSlugger $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger('fr');
    }

    /**
     * @return list<ImportedRider>
     */
    public function riders(): array
    {
        $riders = [];

        foreach ($this->startList() as $team => $teamRiders) {
            foreach ($teamRiders as [$name, $nationality]) {
                $riders[] = new ImportedRider(
                    strtolower((string) $this->slugger->slug($name)),
                    $name,
                    $team,
                    $nationality,
                    0,
                    null,
                );
            }
        }

        return $riders;
    }

    /**
     * @return array<string, list<array{string, string}>>
     */
    private function startList(): array
    {
        return [
            'Alpecin-Premier Tech' => [
                ['Mathieu van der Poel', 'Netherlands'],
                ['Ramses Debruyne', 'Belgium'],
                ['Silvan Dillier', 'Switzerland'],
                ['Tim Marsman', 'Netherlands'],
                ['Jasper Philipsen', 'Belgium'],
                ['Edward Plackaert', 'Belgium'],
                ['Jonas Rickaert', 'Belgium'],
                ['Emiel Verstrynge', 'Belgium'],
            ],
            'Bahrain Victorious' => [
                ['Antonio Tiberi', 'Italy'],
                ['Phil Bauhaus', 'Germany'],
                ['Damiano Caruso', 'Italy'],
                ['Kamil Gradek', 'Poland'],
                ['Lenny Martinez', 'France'],
                ['Matej Mohoric', 'Slovenia'],
                ['Robert Stannard', 'Australia'],
                ['Vlad Van Mechelen', 'Belgium'],
            ],
            'Caja Rural-Seguros RGA' => [
                ['Abel Balderstone', 'Spain'],
                ['Sebastian Berwick', 'Australia'],
                ['Fernando Gaviria', 'Colombia'],
                ['Alex Molenaar', 'Netherlands'],
                ['Joel Nicolau', 'Spain'],
                ['Stefano Oldani', 'Italy'],
                ['Jakub Otruba', 'Czech Republic'],
                ['Jose Felix Parra', 'Spain'],
            ],
            'Cofidis' => [
                ['Piet Allegaert', 'Belgium'],
                ['Alex Aranburu', 'Spain'],
                ['Jenthe Biermans', 'Belgium'],
                ['Ion Izagirre', 'Spain'],
                ['Milan Fretin', 'Belgium'],
                ['Alex Kirsch', 'Luxembourg'],
                ['Hugo Page', 'France'],
                ['Benjamin Thomas', 'France'],
            ],
            'Decathlon CMA CGM' => [
                ['Paul Seixas', 'France'],
                ['Tiesj Benoot', 'Belgium'],
                ['Cees Bol', 'Netherlands'],
                ['Daan Hoole', 'Netherlands'],
                ['Olav Kooij', 'Netherlands'],
                ['Aurelien Paret-Peintre', 'France'],
                ['Nicolas Prodhomme', 'France'],
                ['Matthew Riccitello', 'United States'],
            ],
            'EF Education-EasyPost' => [
                ['Richard Carapaz', 'Ecuador'],
                ['Ben Healy', 'Ireland'],
                ['Kasper Asgreen', 'Denmark'],
                ['Michael Valgren', 'Denmark'],
                ['Alex Baudin', 'France'],
                ['Sean Quinn', 'United States'],
                ['Max Walker', 'Great Britain'],
                ['Georg Steinhauser', 'Germany'],
            ],
            'Groupama-FDJ United' => [
                ['Clement Berthet', 'France'],
                ['Clement Braz Afonso', 'France'],
                ['Ewen Costiou', 'France'],
                ['Lorenzo Germani', 'Italy'],
                ['Romain Gregoire', 'France'],
                ['Guillaume Martin-Guyonnet', 'France'],
                ['Quentin Pacher', 'France'],
                ['Clement Russo', 'France'],
            ],
            'Intermarche-Lotto' => [
                ['Huub Artz', 'Netherlands'],
                ['Jenno Berckmoes', 'Belgium'],
                ['Lars Craps', 'Belgium'],
                ['Arnaud De Lie', 'Belgium'],
                ['Liam Slock', 'Belgium'],
                ['Lennert Van Eetvelt', 'Belgium'],
                ['Baptiste Veistroffer', 'France'],
                ['Georg Zimmermann', 'Germany'],
            ],
            'Jayco AlUla' => [
                ['Pascal Ackermann', 'Germany'],
                ['Luke Durbridge', 'Australia'],
                ['Felix Engelhardt', 'Germany'],
                ['Kelland OBrien', 'Australia'],
                ['Ben OConnor', 'Australia'],
                ['Michael Matthews', 'Australia'],
                ['Luke Plapp', 'Australia'],
                ['Mauro Schmid', 'Switzerland'],
            ],
            'Lidl-Trek' => [
                ['Juan Ayuso', 'Spain'],
                ['Derek Gee-West', 'Canada'],
                ['Mads Pedersen', 'Denmark'],
                ['Quinn Simmons', 'United States'],
                ['Mattias Skjelmose', 'Denmark'],
                ['Toms Skujins', 'Latvia'],
                ['Mathias Vacek', 'Czech Republic'],
                ['Carlos Verona', 'Spain'],
            ],
            'Movistar' => [
                ['Cian Uijtdebroeks', 'Belgium'],
                ['Pablo Castrillo', 'Spain'],
                ['Alveiro Cepeda', 'Colombia'],
                ['Raul Garcia Pierna', 'Spain'],
                ['Michel Hessmann', 'Germany'],
                ['Nelson Oliveira', 'Portugal'],
                ['Javier Romo', 'Spain'],
                ['Einer Rubio', 'Colombia'],
            ],
            'Netcompany-Ineos' => [
                ['Thymen Arensman', 'Netherlands'],
                ['Egan Bernal', 'Colombia'],
                ['Tobias Foss', 'Norway'],
                ['Filippo Ganna', 'Italy'],
                ['Dorian Godon', 'France'],
                ['Michal Kwiatkowski', 'Poland'],
                ['Josh Tarling', 'Great Britain'],
                ['Kevin Vauquelin', 'France'],
            ],
            'NSN Cycling' => [
                ['Biniam Girmay', 'Eritrea'],
                ['Lewis Askey', 'Great Britain'],
                ['George Bennett', 'New Zealand'],
                ['Marco Frigo', 'Italy'],
                ['Matis Louvel', 'France'],
                ['Krists Neilands', 'Latvia'],
                ['Jake Stewart', 'Great Britain'],
                ['Tom Van Asbroeck', 'Belgium'],
            ],
            'Picnic PostNL' => [
                ['Warren Barguil', 'France'],
                ['Frits Biesterbos', 'Netherlands'],
                ['Pavel Bittner', 'Czech Republic'],
                ['John Degenkolb', 'Germany'],
                ['Robbe Dhondt', 'Belgium'],
                ['Niklas Markl', 'Germany'],
                ['Julius van den Berg', 'Netherlands'],
                ['Frank van den Broek', 'Netherlands'],
            ],
            'Pinarello Q36.5 Pro Cycling' => [
                ['Tom Pidcock', 'Great Britain'],
                ['Xabier Mikel Azparren', 'Spain'],
                ['Chris Harper', 'Australia'],
                ['Quinten Hermans', 'Belgium'],
                ['Damien Howson', 'Australia'],
                ['Xandro Meurisse', 'Belgium'],
                ['Brent Van Moer', 'Belgium'],
                ['Fred Wright', 'Great Britain'],
            ],
            'Red Bull-Bora-hansgrohe' => [
                ['Remco Evenepoel', 'Belgium'],
                ['Florian Lipowitz', 'Germany'],
                ['Mattia Cattaneo', 'Italy'],
                ['Nico Denz', 'Germany'],
                ['Jai Hindley', 'Australia'],
                ['Jan Tratnik', 'Slovenia'],
                ['Tim van Dijke', 'Netherlands'],
                ['Maxim Van Gils', 'Belgium'],
            ],
            'Soudal Quick-Step' => [
                ['Pascal Eenkhoorn', 'Netherlands'],
                ['Tim Merlier', 'Belgium'],
                ['Valentin Paret-Peintre', 'France'],
                ['Jasper Stuyven', 'Belgium'],
                ['Dylan van Baarle', 'Netherlands'],
                ['Bert Van Lerberghe', 'Belgium'],
                ['Ilan van Wilder', 'Belgium'],
                ['Louis Vervaeke', 'Belgium'],
            ],
            'TotalEnergies' => [
                ['Nicolas Breuillard', 'France'],
                ['Joris Delbove', 'France'],
                ['Alexandre Delettre', 'France'],
                ['Thibault Guernalec', 'France'],
                ['Jordan Jegat', 'France'],
                ['Mathis Le Berre', 'France'],
                ['Anthony Turgis', 'France'],
                ['Matteo Vercher', 'France'],
            ],
            'Tudor Pro Cycling' => [
                ['Julian Alaphilippe', 'France'],
                ['Marco Haller', 'Austria'],
                ['Marc Hirschi', 'Switzerland'],
                ['Arvid de Kleijn', 'Netherlands'],
                ['Rick Pluimers', 'Netherlands'],
                ['Michael Storer', 'Australia'],
                ['Matteo Trentin', 'Italy'],
                ['Yannis Voisard', 'Switzerland'],
            ],
            'UAE Team Emirates-XRG' => [
                ['Tadej Pogacar', 'Slovenia'],
                ['Isaac del Toro', 'Mexico'],
                ['Felix Grossschartner', 'Austria'],
                ['Brandon McNulty', 'United States'],
                ['Nils Politt', 'Germany'],
                ['Florian Vermeersch', 'Belgium'],
                ['Tim Wellens', 'Belgium'],
                ['Adam Yates', 'Great Britain'],
            ],
            'Uno-X Mobility' => [
                ['Tobias Halland Johannessen', 'Norway'],
                ['Jonas Abrahamsen', 'Norway'],
                ['Anthon Charmig', 'Denmark'],
                ['Magnus Cort', 'Denmark'],
                ['Andreas Kron', 'Denmark'],
                ['Anders Skaarseth', 'Norway'],
                ['Torstein Traeen', 'Norway'],
                ['Soren Waerenskjold', 'Norway'],
            ],
            'Visma-Lease a Bike' => [
                ['Jonas Vingegaard', 'Denmark'],
                ['Edoardo Affini', 'Italy'],
                ['Bruno Armirail', 'France'],
                ['Victor Campenaerts', 'Belgium'],
                ['Matteo Jorgenson', 'United States'],
                ['Sepp Kuss', 'United States'],
                ['Davide Piganzoli', 'Italy'],
                ['Per Strand Hagenes', 'Norway'],
            ],
            'XDS Astana' => [
                ['Davide Ballerini', 'Italy'],
                ['Aaron Gate', 'New Zealand'],
                ['Sergio Higuita', 'Colombia'],
                ['Max Kanter', 'Germany'],
                ['Harold Tejada', 'Colombia'],
                ['Mike Teunissen', 'Netherlands'],
                ['Simone Velasco', 'Italy'],
                ['Nicolas Vinokurov', 'Kazakhstan'],
            ],
        ];
    }
}
