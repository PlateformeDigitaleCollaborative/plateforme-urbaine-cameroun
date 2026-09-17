<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Supprime réellement l'ancienne contrainte d'unicité
 * uniq_space_highlight_space_year (space_id, year) sur
 * divercity.space_highlight, remplacée par
 * uniq_space_highlight_space_year_semester (space_id, year, semester).
 *
 * Les migrations précédentes émettaient « DROP INDEX IF EXISTS
 * uniq_space_highlight_space_year » sans qualifier le schéma : PostgreSQL
 * résolvait le nom via le search_path (public), ne trouvait rien, et le
 * IF EXISTS rendait l'ordre silencieux. L'index restait donc en place dans
 * le schéma divercity — d'où sa réapparition dans chaque diff Doctrine.
 */
final class Version20260917150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop the stale uniq_space_highlight_space_year constraint/index in the divercity schema';
    }

    public function up(Schema $schema): void
    {
        // Selon la manière dont elle a été créée, l'unicité peut être une
        // vraie contrainte de table ou un simple index unique : on traite
        // les deux cas. Si la contrainte existe, son index est supprimé avec
        // elle et le DROP INDEX suivant devient un no-op.
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_highlight DROP CONSTRAINT IF EXISTS uniq_space_highlight_space_year
        SQL);

        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS divercity.uniq_space_highlight_space_year
        SQL);

        // Filet de sécurité : l'index attendu par l'entité SpaceHighlight.
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX IF NOT EXISTS uniq_space_highlight_space_year_semester ON divercity.space_highlight (space_id, year, semester)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Attention : ce down() échouera si la table contient déjà plusieurs
        // semestres pour une même année sur un même espace.
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS divercity.uniq_space_highlight_space_year_semester
        SQL);

        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_space_highlight_space_year ON divercity.space_highlight (space_id, year)
        SQL);
    }
}