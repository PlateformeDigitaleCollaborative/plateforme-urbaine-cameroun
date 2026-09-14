<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260913172716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE connection_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE page_view_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE connection_log (id INT NOT NULL, user_id INT NOT NULL, connected_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_93E1E0FFA76ED395 ON connection_log (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_connection_log_connected_at ON connection_log (connected_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN connection_log.connected_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE page_view (id INT NOT NULL, path VARCHAR(500) NOT NULL, visitor_hash VARCHAR(64) NOT NULL, viewed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_page_view_viewed_at ON page_view (viewed_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_page_view_path ON page_view (path)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN page_view.viewed_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE connection_log ADD CONSTRAINT FK_93E1E0FFA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS idx_booking_attachment_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS idx_booking_attachment_media_object
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
            DROP SEQUENCE connection_log_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE page_view_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE connection_log DROP CONSTRAINT FK_93E1E0FFA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE connection_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE page_view
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_space_highlight_space_year ON divercity.space_highlight (space_id, year)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_booking_attachment_type ON divercity.booking_attachment (booking_id, type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_booking_attachment_media_object ON divercity.booking_attachment (file_object_id)
        SQL);
    }
}
