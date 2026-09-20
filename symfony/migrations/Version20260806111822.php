<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260806111822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sync DiverCity module entities (Space, Booking, BlockedPeriod, EventActivityFavorite, Notification, SpaceAdmin, Status, EventActivityType, InformationSource) with existing divercity schema';
    }

    public function up(Schema $schema): void
    {
        $legacyIndex = $this->connection->fetchOne("SELECT to_regclass('divercity.idx_blocked_period_date')");
        $this->skipIf(null === $legacyIndex || false === $legacyIndex, 'Schéma divercity déjà à jour (créé par Version20260805000000).');
        
        $this->addSql(<<<'SQL'
            DROP INDEX divercity.idx_blocked_period_date
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.blocked_period ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.blocked_period ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.blocked_period ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.blocked_period.space_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_blocked_period_space RENAME TO IDX_FB43A95023575340
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_blocked_period_created_by RENAME TO IDX_FB43A950DE12AB56
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX divercity.idx_booking_submitted
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX divercity.idx_booking_date_status
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX divercity.idx_booking_conflict
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking ALTER submitted_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking.space_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_space RENAME TO IDX_9D23201D23575340
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_user RENAME TO IDX_9D23201DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_processing_user RENAME TO IDX_9D23201DCC36FF0E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_status RENAME TO IDX_9D23201D6BF700BD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_type RENAME TO IDX_9D23201DE30E19BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_source RENAME TO IDX_9D23201D2BB019E3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object DROP CONSTRAINT booking_media_object_media_object_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object DROP CONSTRAINT booking_media_object_booking_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object ALTER booking_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_media_object.booking_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object ADD CONSTRAINT FK_8EBEA4483301C60 FOREIGN KEY (booking_id) REFERENCES divercity.booking (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object ADD CONSTRAINT FK_8EBEA44864DE5A5 FOREIGN KEY (media_object_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_mo_media_object RENAME TO IDX_8EBEA44864DE5A5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource DROP CONSTRAINT booking_resource_resource_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource DROP CONSTRAINT booking_resource_booking_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ALTER booking_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ALTER resource_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_resource.booking_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_resource.resource_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ADD CONSTRAINT FK_6905509A3301C60 FOREIGN KEY (booking_id) REFERENCES divercity.booking (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ADD CONSTRAINT FK_6905509A89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_booking_resource_res RENAME TO IDX_6905509A89329D25
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_favorite ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_favorite ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_favorite ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.event_activity_favorite.space_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_favorite_space RENAME TO IDX_1847E25423575340
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_favorite_user RENAME TO IDX_1847E254A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_favorite_media_object RENAME TO IDX_1847E25464DE5A5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_type ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.event_activity_type_label_key RENAME TO UNIQ_59FE2B5FEA750E8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.information_source ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.information_source_label_key RENAME TO UNIQ_62D8C30EA750E8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX divercity.idx_notification_date
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.notification ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.notification ALTER booking_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.notification ALTER sent_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.notification.booking_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_notification_booking RENAME TO IDX_3C46D47C3301C60
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_notification_user RENAME TO IDX_3C46D47CA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER is_validated DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER updated_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.space_slug_key RENAME TO UNIQ_78B8D002989D9B62
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_space_created_by RENAME TO IDX_78B8D002DE12AB56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_space_updated_by RENAME TO IDX_78B8D00216FE72E1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.space_geo_data_id_key RENAME TO UNIQ_78B8D00280E32C3E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object DROP CONSTRAINT space_media_object_space_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object DROP CONSTRAINT space_media_object_media_object_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.space_media_object.space_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object ADD CONSTRAINT FK_72B9899623575340 FOREIGN KEY (space_id) REFERENCES divercity.space (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object ADD CONSTRAINT FK_72B9899664DE5A5 FOREIGN KEY (media_object_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_space_media_object_mo RENAME TO IDX_72B9899664DE5A5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_admin ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_admin ALTER assigned_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.space_admin.space_id IS '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_space_admin_user RENAME TO IDX_442F6025A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_space_admin_space RENAME TO IDX_442F602523575340
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.status ALTER id DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.status_code_key RENAME TO UNIQ_5353179377153098
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.event_activity_type_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('divercity.event_activity_type_id_seq', (SELECT MAX(id) FROM divercity.event_activity_type))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_type ALTER id SET DEFAULT nextval('divercity.event_activity_type_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.uniq_59fe2b5fea750e8 RENAME TO event_activity_type_label_key
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.notification_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('divercity.notification_id_seq', (SELECT MAX(id) FROM divercity.notification))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.notification ALTER id SET DEFAULT nextval('divercity.notification_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.notification ALTER booking_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.notification ALTER sent_at SET DEFAULT 'now()'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.notification.booking_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_notification_date ON divercity.notification (sent_at)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_3c46d47ca76ed395 RENAME TO idx_notification_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_3c46d47c3301c60 RENAME TO idx_notification_booking
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.information_source_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('divercity.information_source_id_seq', (SELECT MAX(id) FROM divercity.information_source))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.information_source ALTER id SET DEFAULT nextval('divercity.information_source_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.uniq_62d8c30ea750e8 RENAME TO information_source_label_key
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object DROP CONSTRAINT FK_8EBEA4483301C60
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object DROP CONSTRAINT FK_8EBEA44864DE5A5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object ALTER booking_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_media_object.booking_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object ADD CONSTRAINT booking_media_object_media_object_id_fkey FOREIGN KEY (media_object_id) REFERENCES media_object (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_media_object ADD CONSTRAINT booking_media_object_booking_id_fkey FOREIGN KEY (booking_id) REFERENCES divercity.booking (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_8ebea44864de5a5 RENAME TO idx_booking_mo_media_object
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_admin ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_admin ALTER assigned_at SET DEFAULT 'now()'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.space_admin.space_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_442f602523575340 RENAME TO idx_space_admin_space
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_442f6025a76ed395 RENAME TO idx_space_admin_user
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.blocked_period_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('divercity.blocked_period_id_seq', (SELECT MAX(id) FROM divercity.blocked_period))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.blocked_period ALTER id SET DEFAULT nextval('divercity.blocked_period_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.blocked_period ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.blocked_period ALTER created_at SET DEFAULT 'now()'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.blocked_period.space_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_blocked_period_date ON divercity.blocked_period (space_id, date)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_fb43a950de12ab56 RENAME TO idx_blocked_period_created_by
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_fb43a95023575340 RENAME TO idx_blocked_period_space
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking ALTER id SET DEFAULT 'gen_random_uuid()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking ALTER submitted_at SET DEFAULT 'now()'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking.space_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_booking_submitted ON divercity.booking (submitted_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_booking_date_status ON divercity.booking (date, status_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_booking_conflict ON divercity.booking (space_id, date, start_time, end_time)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_9d23201d2bb019e3 RENAME TO idx_booking_source
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_9d23201de30e19bb RENAME TO idx_booking_type
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_9d23201d6bf700bd RENAME TO idx_booking_status
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_9d23201da76ed395 RENAME TO idx_booking_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_9d23201d23575340 RENAME TO idx_booking_space
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_9d23201dcc36ff0e RENAME TO idx_booking_processing_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource DROP CONSTRAINT FK_6905509A3301C60
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource DROP CONSTRAINT FK_6905509A89329D25
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ALTER booking_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ALTER resource_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_resource.booking_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_resource.resource_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ADD CONSTRAINT booking_resource_resource_id_fkey FOREIGN KEY (resource_id) REFERENCES resource (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.booking_resource ADD CONSTRAINT booking_resource_booking_id_fkey FOREIGN KEY (booking_id) REFERENCES divercity.booking (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_6905509a89329d25 RENAME TO idx_booking_resource_res
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object DROP CONSTRAINT FK_72B9899623575340
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object DROP CONSTRAINT FK_72B9899664DE5A5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.space_media_object.space_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object ADD CONSTRAINT space_media_object_space_id_fkey FOREIGN KEY (space_id) REFERENCES divercity.space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space_media_object ADD CONSTRAINT space_media_object_media_object_id_fkey FOREIGN KEY (media_object_id) REFERENCES media_object (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_72b9899664de5a5 RENAME TO idx_space_media_object_mo
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER id SET DEFAULT 'gen_random_uuid()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER created_at SET DEFAULT 'now()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER updated_at SET DEFAULT 'now()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.space ALTER is_validated SET DEFAULT false
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.uniq_78b8d002989d9b62 RENAME TO space_slug_key
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.uniq_78b8d00280e32c3e RENAME TO space_geo_data_id_key
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_78b8d00216fe72e1 RENAME TO idx_space_updated_by
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_78b8d002de12ab56 RENAME TO idx_space_created_by
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.status_id_seq
        SQL);
        $this->addSql(<<<'SQL'
            SELECT setval('divercity.status_id_seq', (SELECT MAX(id) FROM divercity.status))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.status ALTER id SET DEFAULT nextval('divercity.status_id_seq')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.uniq_5353179377153098 RENAME TO status_code_key
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_favorite ALTER id SET DEFAULT 'gen_random_uuid()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_favorite ALTER space_id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE divercity.event_activity_favorite ALTER created_at SET DEFAULT 'now()'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.event_activity_favorite.space_id IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_1847e25464de5a5 RENAME TO idx_favorite_media_object
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_1847e254a76ed395 RENAME TO idx_favorite_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX divercity.idx_1847e25423575340 RENAME TO idx_favorite_space
        SQL);
    }
}
