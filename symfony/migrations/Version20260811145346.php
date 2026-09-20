<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811145346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remplace booking_media_object par booking_attachment (pièces jointes typées).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS divercity.booking_media_object');

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.booking_attachment (
                id                      SERIAL PRIMARY KEY,
                booking_id              uuid NOT NULL REFERENCES divercity.booking(id) ON DELETE CASCADE,
                file_object_id          INTEGER NOT NULL REFERENCES public.media_object(id) ON DELETE CASCADE,
                type                    VARCHAR(30) NOT NULL,
                created_at              TIMESTAMP(0) NOT NULL DEFAULT now(),
                CONSTRAINT chk_booking_attachment_type
                    CHECK (type IN ('AGENDA', 'RESOURCE_DOCUMENT', 'OTHER'))
            )
        SQL);

        $this->addSql('CREATE INDEX idx_booking_attachment_booking ON divercity.booking_attachment(booking_id)');
        $this->addSql('CREATE INDEX idx_booking_attachment_media_object ON divercity.booking_attachment(file_object_id)');
        $this->addSql('CREATE INDEX idx_booking_attachment_type ON divercity.booking_attachment(booking_id, type)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS divercity.booking_attachment');

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.booking_media_object (
                booking_id       uuid NOT NULL,
                media_object_id  INTEGER NOT NULL,
                PRIMARY KEY (booking_id, media_object_id)
            )
        SQL);
    }
}
