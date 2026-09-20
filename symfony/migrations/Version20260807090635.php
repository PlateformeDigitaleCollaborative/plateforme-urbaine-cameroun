<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807090635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE IF EXISTS admin2_boundary_id_seq CASCADE
        SQL);
        $this->setNotNullIfNoNulls('actor');
        $this->createBanocUniqueIndexIfNoDuplicates('actor', 'UNIQ_447556F9EC3D194B');
        $this->createBanocUniqueIndexIfNoDuplicates('project', 'UNIQ_2FB3D0EEEC3D194B');
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_tokens ALTER id DROP DEFAULT
        SQL);
        $this->setNotNullIfNoNulls('resource');
        $this->createBanocUniqueIndexIfNoDuplicates('resource', 'UNIQ_BC91F416EC3D194B');
    }

    /**
     * Rend administrative_scopes obligatoire seulement si aucune ligne n'est vide.
     * Des lignes vides existent dans les données de production : voir le rapport de déploiement.
     */
    private function setNotNullIfNoNulls(string $table): void
    {
        $nulls = (int) $this->connection->fetchOne(
            sprintf('SELECT count(*) FROM %s WHERE administrative_scopes IS NULL', $table)
        );

        if (0 === $nulls) {
            $this->addSql(sprintf('ALTER TABLE %s ALTER administrative_scopes SET NOT NULL', $table));
        }
    }

    /**
     * Crée l'index unique sur banoc_url seulement si aucune valeur n'est en double.
     * Des doublons (chaîne vide) existent dans les données de production : voir le rapport de déploiement.
     */
    private function createBanocUniqueIndexIfNoDuplicates(string $table, string $indexName): void
    {
        $duplicates = (int) $this->connection->fetchOne(
            sprintf(
                'SELECT count(*) FROM (SELECT banoc_url FROM %s WHERE banoc_url IS NOT NULL GROUP BY banoc_url HAVING count(*) > 1) d',
                $table
            )
        );

        if (0 === $duplicates) {
            $this->addSql(sprintf('CREATE UNIQUE INDEX IF NOT EXISTS %s ON %s (banoc_url)', $indexName, $table));
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE admin2_boundary_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE refresh_tokens_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('refresh_tokens_id_seq', (SELECT MAX(id) FROM refresh_tokens))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_tokens ALTER id SET DEFAULT nextval('refresh_tokens_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_2FB3D0EEEC3D194B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_447556F9EC3D194B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE actor ALTER administrative_scopes DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_BC91F416EC3D194B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE resource ALTER administrative_scopes DROP NOT NULL
        SQL);
    }
}
