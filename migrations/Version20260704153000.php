<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track whether a rider is still racing.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rider ADD is_still_racing BOOLEAN NOT NULL DEFAULT TRUE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rider DROP is_still_racing');
    }
}
