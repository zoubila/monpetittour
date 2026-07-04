<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Persist riders and store fantasy team composition with rider IDs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rider (id SERIAL NOT NULL, slug VARCHAR(120) NOT NULL, name VARCHAR(120) NOT NULL, real_team VARCHAR(120) NOT NULL, nationality VARCHAR(80) NOT NULL, market_value_in_euros INT NOT NULL, specialty VARCHAR(40) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_rider_slug ON rider (slug)');
        $this->addSql('CREATE TABLE fantasy_team_rider (fantasy_team_record_id INT NOT NULL, rider_record_id INT NOT NULL, PRIMARY KEY(fantasy_team_record_id, rider_record_id))');
        $this->addSql('CREATE INDEX IDX_61AFAC8FD596A210 ON fantasy_team_rider (fantasy_team_record_id)');
        $this->addSql('CREATE INDEX IDX_61AFAC8FE6BE1647 ON fantasy_team_rider (rider_record_id)');
        $this->addSql('ALTER TABLE fantasy_team_rider ADD CONSTRAINT FK_61AFAC8FD596A210 FOREIGN KEY (fantasy_team_record_id) REFERENCES fantasy_team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fantasy_team_rider ADD CONSTRAINT FK_61AFAC8FE6BE1647 FOREIGN KEY (rider_record_id) REFERENCES rider (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fantasy_team DROP rider_slugs');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fantasy_team_rider DROP CONSTRAINT FK_61AFAC8FD596A210');
        $this->addSql('ALTER TABLE fantasy_team_rider DROP CONSTRAINT FK_61AFAC8FE6BE1647');
        $this->addSql('DROP TABLE fantasy_team_rider');
        $this->addSql('DROP TABLE rider');
        $this->addSql('ALTER TABLE fantasy_team ADD rider_slugs JSON NOT NULL');
    }
}
