<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Use the official stage number as the rider result foreign key.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage_rider_result DROP CONSTRAINT FK_C2777B22298D193');
        $this->addSql('DROP INDEX IDX_C2777B22298D193');
        $this->addSql('DROP INDEX uniq_stage_rider_result');
        $this->addSql('ALTER TABLE stage RENAME COLUMN number TO stage_number');
        $this->addSql('DROP INDEX uniq_stage_number');
        $this->addSql('ALTER TABLE stage DROP CONSTRAINT stage_pkey');
        $this->addSql('ALTER TABLE stage ADD PRIMARY KEY (stage_number)');
        $this->addSql('ALTER TABLE stage_rider_result ADD stage_number INT');
        $this->addSql('UPDATE stage_rider_result SET stage_number = stage.stage_number FROM stage WHERE stage_rider_result.stage_id = stage.id');
        $this->addSql('ALTER TABLE stage_rider_result ALTER stage_number SET NOT NULL');
        $this->addSql('ALTER TABLE stage_rider_result DROP stage_id');
        $this->addSql('ALTER TABLE stage DROP id');
        $this->addSql('CREATE UNIQUE INDEX uniq_stage_rider_result ON stage_rider_result (stage_number, rider_id)');
        $this->addSql('CREATE INDEX IDX_STAGE_RIDER_RESULT_STAGE_NUMBER ON stage_rider_result (stage_number)');
        $this->addSql('ALTER TABLE stage_rider_result ADD CONSTRAINT FK_STAGE_RIDER_RESULT_STAGE_NUMBER FOREIGN KEY (stage_number) REFERENCES stage (stage_number) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage_rider_result DROP CONSTRAINT FK_STAGE_RIDER_RESULT_STAGE_NUMBER');
        $this->addSql('DROP INDEX IDX_STAGE_RIDER_RESULT_STAGE_NUMBER');
        $this->addSql('DROP INDEX uniq_stage_rider_result');
        $this->addSql('ALTER TABLE stage ADD id INT');
        $this->addSql('UPDATE stage SET id = stage_number');
        $this->addSql('ALTER TABLE stage ALTER id SET NOT NULL');
        $this->addSql('ALTER TABLE stage_rider_result ADD stage_id INT');
        $this->addSql('UPDATE stage_rider_result SET stage_id = stage.id FROM stage WHERE stage_rider_result.stage_number = stage.stage_number');
        $this->addSql('ALTER TABLE stage_rider_result ALTER stage_id SET NOT NULL');
        $this->addSql('ALTER TABLE stage_rider_result DROP stage_number');
        $this->addSql('ALTER TABLE stage DROP CONSTRAINT stage_pkey');
        $this->addSql('ALTER TABLE stage ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE stage RENAME COLUMN stage_number TO number');
        $this->addSql('CREATE UNIQUE INDEX uniq_stage_number ON stage (number)');
        $this->addSql('CREATE UNIQUE INDEX uniq_stage_rider_result ON stage_rider_result (stage_id, rider_id)');
        $this->addSql('CREATE INDEX IDX_C2777B22298D193 ON stage_rider_result (stage_id)');
        $this->addSql('ALTER TABLE stage_rider_result ADD CONSTRAINT FK_C2777B22298D193 FOREIGN KEY (stage_id) REFERENCES stage (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
