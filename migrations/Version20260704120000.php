<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create application users used by the local username/password authentication flow.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE application_user (id SERIAL NOT NULL, username VARCHAR(80) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_application_user_username ON application_user (username)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE application_user');
    }
}
