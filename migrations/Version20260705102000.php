<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705102000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store stage distances with decimal precision.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage ALTER distance_in_kilometers TYPE DOUBLE PRECISION');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage ALTER distance_in_kilometers TYPE INT');
    }
}
