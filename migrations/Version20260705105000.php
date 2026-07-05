<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705105000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store rider stage result gaps from the stage winner.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage_rider_result ADD gap_in_seconds INT DEFAULT 0 NOT NULL');
        $this->addSql(<<<'SQL'
UPDATE stage_rider_result AS result
SET gap_in_seconds = result.time_in_seconds - stage_best.best_time_in_seconds
FROM (
    SELECT stage_number, MIN(time_in_seconds) AS best_time_in_seconds
    FROM stage_rider_result
    GROUP BY stage_number
) AS stage_best
WHERE result.stage_number = stage_best.stage_number
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stage_rider_result DROP gap_in_seconds');
    }
}
