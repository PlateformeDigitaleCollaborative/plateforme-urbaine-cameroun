<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration de base du schéma DiverCity.
 *
 * Le schéma "divercity" a d'abord été créé à la main (hors migrations) sur les bases de dev et d'UAT,
 * puis aligné sur les entités par Version20260806111822. Une base qui n'a jamais eu ce schéma
 * (production, installation from scratch) ne pouvait donc pas rejouer la chaîne de migrations.
 *
 * Cette migration crée le schéma dans l'état où Version20260806111822 le laisse
 * (12 tables, sans les évolutions ultérieures, qui restent dans leurs propres migrations).
 * Elle se saute d'elle-même si le schéma existe déjà (dev, UAT).
 * Version20260806111822 se saute à son tour quand cette migration vient de créer le schéma.
 *
 * SQL généré à partir de schema_pdc.sql (schéma seul, sans données) : seuls les objets du schéma divercity sont repris.
 */
final class Version20260805000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée le schéma DiverCity (état de Version20260806111822) sur les bases qui ne le possèdent pas encore.';
    }

    public function up(Schema $schema): void
    {
        $exists = $this->connection->fetchOne(
            "SELECT 1 FROM information_schema.schemata WHERE schema_name = 'divercity'"
        );
        $this->skipIf(false !== $exists && null !== $exists, 'Le schéma divercity existe déjà : rien à créer.');

        $this->addSql(<<<'SQL'
            CREATE SCHEMA divercity
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.blocked_period (
                id integer NOT NULL,
                space_id uuid NOT NULL,
                created_by integer,
                date date NOT NULL,
                start_time time without time zone NOT NULL,
                end_time time without time zone NOT NULL,
                reason character varying(255),
                is_unblocked boolean DEFAULT false NOT NULL,
                created_at timestamp(0) without time zone NOT NULL,
                CONSTRAINT chk_blocked_period_time CHECK ((end_time > start_time))
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.blocked_period.space_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.blocked_period_id_seq
                AS integer
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1
        SQL);

        $this->addSql(<<<'SQL'
            ALTER SEQUENCE divercity.blocked_period_id_seq OWNED BY divercity.blocked_period.id
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.booking (
                id uuid NOT NULL,
                space_id uuid NOT NULL,
                user_id integer NOT NULL,
                processing_user_id integer,
                status_id integer NOT NULL,
                event_activity_type_id integer,
                information_source_id integer,
                title character varying(200),
                last_name character varying(100) NOT NULL,
                first_name character varying(100) NOT NULL,
                organization character varying(150),
                email character varying(150) NOT NULL,
                phone character varying(30) NOT NULL,
                booking_purpose text NOT NULL,
                date date NOT NULL,
                start_time time without time zone NOT NULL,
                end_time time without time zone NOT NULL,
                participant_count integer NOT NULL,
                additional_information text,
                refusal_reason text,
                cancellation_reason text,
                submitted_at timestamp(0) without time zone NOT NULL,
                processed_at timestamp(0) without time zone,
                CONSTRAINT booking_participant_count_check CHECK ((participant_count > 0)),
                CONSTRAINT chk_booking_email CHECK (((email)::text ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'::text)),
                CONSTRAINT chk_booking_time CHECK ((end_time > start_time))
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking.id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking.space_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.booking_media_object (
                booking_id uuid NOT NULL,
                media_object_id integer NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_media_object.booking_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.booking_resource (
                booking_id uuid NOT NULL,
                resource_id uuid NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_resource.booking_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.booking_resource.resource_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.event_activity_favorite (
                id uuid NOT NULL,
                space_id uuid NOT NULL,
                user_id integer NOT NULL,
                media_object_id integer,
                title character varying(200) NOT NULL,
                start_date date,
                end_date date,
                description text,
                created_at timestamp(0) without time zone NOT NULL,
                CONSTRAINT chk_favorite_dates CHECK (((end_date IS NULL) OR (end_date >= start_date)))
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.event_activity_favorite.id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.event_activity_favorite.space_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.event_activity_type (
                id integer NOT NULL,
                label character varying(150) NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.event_activity_type_id_seq
                AS integer
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1
        SQL);

        $this->addSql(<<<'SQL'
            ALTER SEQUENCE divercity.event_activity_type_id_seq OWNED BY divercity.event_activity_type.id
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.information_source (
                id integer NOT NULL,
                label character varying(150) NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.information_source_id_seq
                AS integer
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1
        SQL);

        $this->addSql(<<<'SQL'
            ALTER SEQUENCE divercity.information_source_id_seq OWNED BY divercity.information_source.id
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.notification (
                id integer NOT NULL,
                booking_id uuid NOT NULL,
                user_id integer,
                type character varying(50) NOT NULL,
                sent_at timestamp(0) without time zone NOT NULL,
                content text
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.notification.booking_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.notification_id_seq
                AS integer
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1
        SQL);

        $this->addSql(<<<'SQL'
            ALTER SEQUENCE divercity.notification_id_seq OWNED BY divercity.notification.id
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.space (
                id uuid NOT NULL,
                created_by integer,
                updated_by integer,
                geo_data_id integer,
                name character varying(150) NOT NULL,
                description text,
                max_capacity integer NOT NULL,
                contact character varying(50),
                email character varying(150),
                video_link character varying(500),
                equipment text,
                slug character varying(128),
                is_validated boolean NOT NULL,
                created_at timestamp(0) without time zone NOT NULL,
                updated_at timestamp(0) without time zone NOT NULL,
                CONSTRAINT space_max_capacity_check CHECK ((max_capacity > 0))
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.space.id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.space_admin (
                user_id integer NOT NULL,
                space_id uuid NOT NULL,
                assigned_at timestamp(0) without time zone NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.space_admin.space_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.space_media_object (
                space_id uuid NOT NULL,
                media_object_id integer NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN divercity.space_media_object.space_id IS '(DC2Type:uuid)'
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE divercity.status (
                id integer NOT NULL,
                code character varying(30) NOT NULL,
                label character varying(100) NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE SEQUENCE divercity.status_id_seq
                AS integer
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1
        SQL);

        $this->addSql(<<<'SQL'
            ALTER SEQUENCE divercity.status_id_seq OWNED BY divercity.status.id
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.blocked_period
                ADD CONSTRAINT blocked_period_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking_media_object
                ADD CONSTRAINT booking_media_object_pkey PRIMARY KEY (booking_id, media_object_id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking
                ADD CONSTRAINT booking_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking_resource
                ADD CONSTRAINT booking_resource_pkey PRIMARY KEY (booking_id, resource_id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.event_activity_favorite
                ADD CONSTRAINT event_activity_favorite_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.event_activity_type
                ADD CONSTRAINT event_activity_type_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.information_source
                ADD CONSTRAINT information_source_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.notification
                ADD CONSTRAINT notification_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space_admin
                ADD CONSTRAINT space_admin_pkey PRIMARY KEY (user_id, space_id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space_media_object
                ADD CONSTRAINT space_media_object_pkey PRIMARY KEY (space_id, media_object_id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space
                ADD CONSTRAINT space_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.status
                ADD CONSTRAINT status_pkey PRIMARY KEY (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.status
                ADD CONSTRAINT uniq_5353179377153098 UNIQUE (code)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.event_activity_type
                ADD CONSTRAINT uniq_59fe2b5fea750e8 UNIQUE (label)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.information_source
                ADD CONSTRAINT uniq_62d8c30ea750e8 UNIQUE (label)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space
                ADD CONSTRAINT uniq_78b8d00280e32c3e UNIQUE (geo_data_id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space
                ADD CONSTRAINT uniq_78b8d002989d9b62 UNIQUE (slug)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_1847e25423575340 ON divercity.event_activity_favorite USING btree (space_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_1847e25464de5a5 ON divercity.event_activity_favorite USING btree (media_object_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_1847e254a76ed395 ON divercity.event_activity_favorite USING btree (user_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_3c46d47c3301c60 ON divercity.notification USING btree (booking_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_3c46d47ca76ed395 ON divercity.notification USING btree (user_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_442f602523575340 ON divercity.space_admin USING btree (space_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_442f6025a76ed395 ON divercity.space_admin USING btree (user_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_6905509a89329d25 ON divercity.booking_resource USING btree (resource_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_72b9899664de5a5 ON divercity.space_media_object USING btree (media_object_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_78b8d00216fe72e1 ON divercity.space USING btree (updated_by)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_78b8d002de12ab56 ON divercity.space USING btree (created_by)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_8ebea44864de5a5 ON divercity.booking_media_object USING btree (media_object_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_9d23201d23575340 ON divercity.booking USING btree (space_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_9d23201d2bb019e3 ON divercity.booking USING btree (information_source_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_9d23201d6bf700bd ON divercity.booking USING btree (status_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_9d23201da76ed395 ON divercity.booking USING btree (user_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_9d23201dcc36ff0e ON divercity.booking USING btree (processing_user_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_9d23201de30e19bb ON divercity.booking USING btree (event_activity_type_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_fb43a95023575340 ON divercity.blocked_period USING btree (space_id)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX idx_fb43a950de12ab56 ON divercity.blocked_period USING btree (created_by)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.blocked_period
                ADD CONSTRAINT blocked_period_created_by_fkey FOREIGN KEY (created_by) REFERENCES public."user"(id) ON DELETE SET NULL
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.blocked_period
                ADD CONSTRAINT blocked_period_space_id_fkey FOREIGN KEY (space_id) REFERENCES divercity.space(id) ON DELETE CASCADE
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking
                ADD CONSTRAINT booking_event_activity_type_id_fkey FOREIGN KEY (event_activity_type_id) REFERENCES divercity.event_activity_type(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking
                ADD CONSTRAINT booking_information_source_id_fkey FOREIGN KEY (information_source_id) REFERENCES divercity.information_source(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking
                ADD CONSTRAINT booking_processing_user_id_fkey FOREIGN KEY (processing_user_id) REFERENCES public."user"(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking
                ADD CONSTRAINT booking_space_id_fkey FOREIGN KEY (space_id) REFERENCES divercity.space(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking
                ADD CONSTRAINT booking_status_id_fkey FOREIGN KEY (status_id) REFERENCES divercity.status(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking
                ADD CONSTRAINT booking_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.event_activity_favorite
                ADD CONSTRAINT event_activity_favorite_media_object_id_fkey FOREIGN KEY (media_object_id) REFERENCES public.media_object(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.event_activity_favorite
                ADD CONSTRAINT event_activity_favorite_space_id_fkey FOREIGN KEY (space_id) REFERENCES divercity.space(id) ON DELETE CASCADE
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.event_activity_favorite
                ADD CONSTRAINT event_activity_favorite_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id) ON DELETE CASCADE
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking_resource
                ADD CONSTRAINT fk_6905509a3301c60 FOREIGN KEY (booking_id) REFERENCES divercity.booking(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking_resource
                ADD CONSTRAINT fk_6905509a89329d25 FOREIGN KEY (resource_id) REFERENCES public.resource(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space_media_object
                ADD CONSTRAINT fk_72b9899623575340 FOREIGN KEY (space_id) REFERENCES divercity.space(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space_media_object
                ADD CONSTRAINT fk_72b9899664de5a5 FOREIGN KEY (media_object_id) REFERENCES public.media_object(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking_media_object
                ADD CONSTRAINT fk_8ebea4483301c60 FOREIGN KEY (booking_id) REFERENCES divercity.booking(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.booking_media_object
                ADD CONSTRAINT fk_8ebea44864de5a5 FOREIGN KEY (media_object_id) REFERENCES public.media_object(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.notification
                ADD CONSTRAINT notification_booking_id_fkey FOREIGN KEY (booking_id) REFERENCES divercity.booking(id) ON DELETE CASCADE
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.notification
                ADD CONSTRAINT notification_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space_admin
                ADD CONSTRAINT space_admin_space_id_fkey FOREIGN KEY (space_id) REFERENCES divercity.space(id) ON DELETE CASCADE
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space_admin
                ADD CONSTRAINT space_admin_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id) ON DELETE CASCADE
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space
                ADD CONSTRAINT space_created_by_fkey FOREIGN KEY (created_by) REFERENCES public."user"(id) ON DELETE SET NULL
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space
                ADD CONSTRAINT space_geo_data_id_fkey FOREIGN KEY (geo_data_id) REFERENCES public.geo_data(id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE ONLY divercity.space
                ADD CONSTRAINT space_updated_by_fkey FOREIGN KEY (updated_by) REFERENCES public."user"(id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Volontairement vide : supprimer le schéma détruirait toutes les données DiverCity.
    }
}