<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the Tour de France stage profile image path.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage ADD map_path VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage DROP map_path');
    }
}
