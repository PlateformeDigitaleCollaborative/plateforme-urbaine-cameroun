<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260915144004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS idx_booking_attachment_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS idx_booking_attachment_media_object
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE connection_log ADD country_code VARCHAR(2) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE connection_log ADD country_name VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_connection_log_country_code ON connection_log (country_code)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS uniq_space_highlight_space_year
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_connection_log_country_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE connection_log DROP country_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE connection_log DROP country_name
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_booking_attachment_type ON divercity.booking_attachment (booking_id, type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_booking_attachment_media_object ON divercity.booking_attachment (file_object_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_space_highlight_space_year ON divercity.space_highlight (space_id, year)
        SQL);
    }
}
