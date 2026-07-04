<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create stages and rider stage results.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE stage (id SERIAL NOT NULL, number INT NOT NULL, start_location VARCHAR(120) NOT NULL, finish_location VARCHAR(120) NOT NULL, distance_in_kilometers INT NOT NULL, positive_elevation_in_meters INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_stage_number ON stage (number)');
        $this->addSql('CREATE TABLE stage_rider_result (id SERIAL NOT NULL, stage_id INT NOT NULL, rider_id INT NOT NULL, time_in_seconds INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_stage_rider_result ON stage_rider_result (stage_id, rider_id)');
        $this->addSql('CREATE INDEX IDX_C2777B22298D193 ON stage_rider_result (stage_id)');
        $this->addSql('CREATE INDEX IDX_C2777B226F877A85 ON stage_rider_result (rider_id)');
        $this->addSql('ALTER TABLE stage_rider_result ADD CONSTRAINT FK_C2777B22298D193 FOREIGN KEY (stage_id) REFERENCES stage (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stage_rider_result ADD CONSTRAINT FK_C2777B226F877A85 FOREIGN KEY (rider_id) REFERENCES rider (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage_rider_result DROP CONSTRAINT FK_C2777B22298D193');
        $this->addSql('ALTER TABLE stage_rider_result DROP CONSTRAINT FK_C2777B226F877A85');
        $this->addSql('DROP TABLE stage_rider_result');
        $this->addSql('DROP TABLE stage');
    }
}
