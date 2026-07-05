<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align imported Tour de France 2026 riders with Letour official stage results names.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE rider SET slug = 'edward-planckaert', name = 'Edward Planckaert' WHERE slug = 'edward-plackaert'");
        $this->addSql("UPDATE rider SET slug = 'lucas-plapp', name = 'Lucas Plapp' WHERE slug = 'luke-plapp'");
        $this->addSql("UPDATE rider SET slug = 'derek-james-gee', name = 'Derek James Gee' WHERE slug = 'derek-gee-west'");
        $this->addSql("UPDATE rider SET slug = 'jefferson-cepeda', name = 'Jefferson Cepeda', nationality = 'Ecuador' WHERE slug = 'alveiro-cepeda'");
        $this->addSql("UPDATE rider SET slug = 'joshua-michael-tarling', name = 'Joshua Michael Tarling' WHERE slug = 'josh-tarling'");
        $this->addSql("UPDATE rider SET slug = 'thomas-jake-stewart', name = 'Thomas Jake Stewart' WHERE slug = 'jake-stewart'");
        $this->addSql("UPDATE rider SET slug = 'christopher-harper', name = 'Christopher Harper' WHERE slug = 'chris-harper'");
        $this->addSql("UPDATE rider SET slug = 'alfred-brockwell-wright', name = 'Alfred Brockwell Wright' WHERE slug = 'fred-wright'");
        $this->addSql("UPDATE rider SET slug = 'anders-halland-johannessen', name = 'Anders Halland Johannessen', nationality = 'Norway' WHERE slug = 'andreas-kron'");
        $this->addSql("UPDATE rider SET slug = 'soeren-waerenskjold', name = 'Soeren Waerenskjold' WHERE slug = 'soren-waerenskjold'");
        $this->addSql("UPDATE rider SET slug = 'nicolya-vinokurov', name = 'Nicolya Vinokurov' WHERE slug = 'nicolas-vinokurov'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE rider SET slug = 'edward-plackaert', name = 'Edward Plackaert' WHERE slug = 'edward-planckaert'");
        $this->addSql("UPDATE rider SET slug = 'luke-plapp', name = 'Luke Plapp' WHERE slug = 'lucas-plapp'");
        $this->addSql("UPDATE rider SET slug = 'derek-gee-west', name = 'Derek Gee-West' WHERE slug = 'derek-james-gee'");
        $this->addSql("UPDATE rider SET slug = 'alveiro-cepeda', name = 'Alveiro Cepeda', nationality = 'Colombia' WHERE slug = 'jefferson-cepeda'");
        $this->addSql("UPDATE rider SET slug = 'josh-tarling', name = 'Josh Tarling' WHERE slug = 'joshua-michael-tarling'");
        $this->addSql("UPDATE rider SET slug = 'jake-stewart', name = 'Jake Stewart' WHERE slug = 'thomas-jake-stewart'");
        $this->addSql("UPDATE rider SET slug = 'chris-harper', name = 'Chris Harper' WHERE slug = 'christopher-harper'");
        $this->addSql("UPDATE rider SET slug = 'fred-wright', name = 'Fred Wright' WHERE slug = 'alfred-brockwell-wright'");
        $this->addSql("UPDATE rider SET slug = 'andreas-kron', name = 'Andreas Kron', nationality = 'Denmark' WHERE slug = 'anders-halland-johannessen'");
        $this->addSql("UPDATE rider SET slug = 'soren-waerenskjold', name = 'Soren Waerenskjold' WHERE slug = 'soeren-waerenskjold'");
        $this->addSql("UPDATE rider SET slug = 'nicolas-vinokurov', name = 'Nicolas Vinokurov' WHERE slug = 'nicolya-vinokurov'");
    }
}
