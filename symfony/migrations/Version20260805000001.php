<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Crée space_highlight et space_statistic (DiverCity) dans l'état où elles étaient
 * juste avant Version20260820160509.
 *
 * Ces deux tables avaient été créées à la main en dev/UAT : aucune migration ne les créait,
 * alors que Version20260820160509 et Version20260913155426 les modifient. Sur une base qui n'a
 * jamais eu le schéma DiverCity (production), ces deux migrations échouaient donc.
 *
 * Les migrations suivantes les amènent à l'état actuel de dev
 * (colonne report_file_object_id, index et clés renommés, colonne semester).
 *
 * Volontairement absent : l'ancien index unique uniq_space_highlight_space_year (space_id, year).
 * Version20260913155426 tente de le supprimer avec un DROP INDEX non qualifié par le schéma, qui ne
 * trouverait rien : l'index resterait et empêcherait d'avoir deux semestres pour la même année.
 * La base de dev ne le possède plus non plus.
 *
 * Se saute d'elle-même si les tables existent déjà (dev, UAT).
 */
final class Version20260805000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée divercity.space_highlight et divercity.space_statistic (état antérieur à Version20260820160509).';
    }

    public function up(Schema $schema): void
    {
        $exists = $this->connection->fetchOne("SELECT to_regclass('divercity.space_highlight')");
        $this->skipIf((bool) $exists, 'divercity.space_highlight existe déjà : rien à créer.');

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.space_highlight (
                id                      SERIAL PRIMARY KEY,
                space_id                uuid NOT NULL REFERENCES divercity.space(id) ON DELETE CASCADE,
                year                    INTEGER NOT NULL,
                report_media_object_id  INTEGER,
                created_at              TIMESTAMP(0) NOT NULL DEFAULT now(),
                updated_at              TIMESTAMP(0) NOT NULL DEFAULT now(),
                CONSTRAINT chk_space_highlight_year CHECK (year >= 2000 AND year <= 2100),
                CONSTRAINT space_highlight_report_media_object_id_fkey
                    FOREIGN KEY (report_media_object_id) REFERENCES public.media_object(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_space_highlight_space ON divercity.space_highlight (space_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.space_statistic (
                id                  SERIAL PRIMARY KEY,
                space_highlight_id  INTEGER NOT NULL REFERENCES divercity.space_highlight(id) ON DELETE CASCADE,
                label               VARCHAR(150) NOT NULL,
                value               VARCHAR(255) NOT NULL,
                "position"          INTEGER NOT NULL DEFAULT 0,
                created_at          TIMESTAMP(0) NOT NULL DEFAULT now()
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_space_statistic_highlight ON divercity.space_statistic (space_highlight_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Volontairement vide : supprimer ces tables détruirait les rapports et statistiques DiverCity.
    }
}
