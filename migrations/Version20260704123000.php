<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create fantasy teams, fantasy leagues, and league participants.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE fantasy_league (id SERIAL NOT NULL, creator_id INT NOT NULL, name VARCHAR(120) NOT NULL, code VARCHAR(12) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_fantasy_league_code ON fantasy_league (code)');
        $this->addSql('CREATE INDEX IDX_D0C7C62661220EA6 ON fantasy_league (creator_id)');
        $this->addSql('CREATE TABLE fantasy_league_participant (fantasy_league_record_id INT NOT NULL, application_user_id INT NOT NULL, PRIMARY KEY(fantasy_league_record_id, application_user_id))');
        $this->addSql('CREATE INDEX IDX_53E419D5A16C7F73 ON fantasy_league_participant (fantasy_league_record_id)');
        $this->addSql('CREATE INDEX IDX_53E419D524C0C1F2 ON fantasy_league_participant (application_user_id)');
        $this->addSql('CREATE TABLE fantasy_team (id SERIAL NOT NULL, owner_id INT NOT NULL, name VARCHAR(120) NOT NULL, rider_slugs JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_fantasy_team_owner ON fantasy_team (owner_id)');
        $this->addSql('ALTER TABLE fantasy_league ADD CONSTRAINT FK_D0C7C62661220EA6 FOREIGN KEY (creator_id) REFERENCES application_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fantasy_league_participant ADD CONSTRAINT FK_53E419D5A16C7F73 FOREIGN KEY (fantasy_league_record_id) REFERENCES fantasy_league (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fantasy_league_participant ADD CONSTRAINT FK_53E419D524C0C1F2 FOREIGN KEY (application_user_id) REFERENCES application_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fantasy_team ADD CONSTRAINT FK_DA01DCEF7E3C61F9 FOREIGN KEY (owner_id) REFERENCES application_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fantasy_league_participant DROP CONSTRAINT FK_53E419D5A16C7F73');
        $this->addSql('ALTER TABLE fantasy_league_participant DROP CONSTRAINT FK_53E419D524C0C1F2');
        $this->addSql('ALTER TABLE fantasy_team DROP CONSTRAINT FK_DA01DCEF7E3C61F9');
        $this->addSql('ALTER TABLE fantasy_league DROP CONSTRAINT FK_D0C7C62661220EA6');
        $this->addSql('DROP TABLE fantasy_league_participant');
        $this->addSql('DROP TABLE fantasy_team');
        $this->addSql('DROP TABLE fantasy_league');
    }
}
