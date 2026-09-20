<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Données de référence DiverCity (statuts de réservation, types d'événement, sources d'information).
 *
 * Ces valeurs n'existaient jusqu'ici que dans les fixtures (StatusFixtures, EventActivityTypeFixtures,
 * InformationSourceFixtures), qui ne doivent JAMAIS être chargées en production (elles purgent la base).
 * Le code applicatif en dépend : BookingSubmissionProcessor cherche le statut EN_ATTENTE et
 * BookingCancellationProcessor le statut ANNULEE.
 *
 * Les colonnes id n'ont pas de DEFAULT en base (Doctrine appelle la séquence lui-même, cf. Version20260806111822) :
 * l'id est donc demandé explicitement à la séquence <table>_id_seq.
 *
 * Migration idempotente : ON CONFLICT DO NOTHING sur les colonnes uniques (code / label),
 * donc sans effet sur un environnement qui contient déjà ces lignes.
 * À placer APRÈS toutes les migrations qui créent ou modifient ces tables (short_label, color).
 */
final class Version20260920160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed idempotent des données de référence DiverCity (status, event_activity_type, information_source).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO divercity.status (id, code, label) VALUES
                (nextval('divercity.status_id_seq'), 'EN_ATTENTE', 'En attente'),
                (nextval('divercity.status_id_seq'), 'EN_COURS_DE_TRAITEMENT', 'En cours de traitement'),
                (nextval('divercity.status_id_seq'), 'ACCEPTEE', 'Acceptée'),
                (nextval('divercity.status_id_seq'), 'REFUSEE', 'Refusée'),
                (nextval('divercity.status_id_seq'), 'ANNULEE', 'Annulée')
            ON CONFLICT (code) DO NOTHING
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO divercity.event_activity_type (id, label, short_label, color) VALUES
                (nextval('divercity.event_activity_type_id_seq'), 'Atelier participatif / Co-création', 'Atelier participatif', '#3B82F6'),
                (nextval('divercity.event_activity_type_id_seq'), 'Conférence / Débat / Table ronde', 'Conférence / Débat', '#8B5CF6'),
                (nextval('divercity.event_activity_type_id_seq'), 'Formation / Renforcement de capacités', 'Formation', '#10B981'),
                (nextval('divercity.event_activity_type_id_seq'), 'Séminaire / Colloque / Journée d''étude', 'Séminaire / Colloque', '#F59E0B'),
                (nextval('divercity.event_activity_type_id_seq'), 'Incubation / Accélération de projets', 'Incubation', '#EC4899'),
                (nextval('divercity.event_activity_type_id_seq'), 'Hackathon / Marathon d''innovation', 'Hackathon', '#EF4444'),
                (nextval('divercity.event_activity_type_id_seq'), 'Événement de réseautage / Partenariat', 'Réseautage', '#06B6D4'),
                (nextval('divercity.event_activity_type_id_seq'), 'Autres', 'Autres', '#6B7280')
            ON CONFLICT (label) DO NOTHING
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO divercity.information_source (id, label) VALUES
                (nextval('divercity.information_source_id_seq'), 'Bouche-à-oreille'),
                (nextval('divercity.information_source_id_seq'), 'Réseau des acteurs de la plateforme Urbaine'),
                (nextval('divercity.information_source_id_seq'), 'Recommandation'),
                (nextval('divercity.information_source_id_seq'), 'Réseaux sociaux')
            ON CONFLICT (label) DO NOTHING
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Volontairement vide : supprimer ces lignes de référence casserait les réservations existantes.
    }
}