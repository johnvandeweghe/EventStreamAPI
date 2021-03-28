<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210328071555 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'initial';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE event (id UUID NOT NULL, user_id VARCHAR(255) NOT NULL, stream_id UUID NOT NULL, event_data_id UUID DEFAULT NULL, transport_id VARCHAR(255) DEFAULT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7A76ED395 ON event (user_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7D0ED463E ON event (stream_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7B2A20893 ON event (event_data_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA79909C13F ON event (transport_id)');
        $this->addSql('CREATE INDEX idx_e_stream_datetime ON event (stream_id, datetime)');
        $this->addSql('CREATE INDEX idx_e_stream_user_datetime ON event (stream_id, user_id, datetime)');
        $this->addSql('CREATE INDEX idx_e_stream_transport_datetime ON event (stream_id, transport_id, datetime)');
        $this->addSql('CREATE INDEX idx_e_stream_type_datetime ON event (stream_id, type, datetime)');
        $this->addSql('COMMENT ON COLUMN event.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.stream_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.event_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.datetime IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE event_data (id UUID NOT NULL, data TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN event_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE invite (id UUID NOT NULL, stream_id UUID NOT NULL, expiration TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7E210D7D0ED463E ON invite (stream_id)');
        $this->addSql('COMMENT ON COLUMN invite.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN invite.stream_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN invite.expiration IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE role (id UUID NOT NULL, stream_id UUID NOT NULL, name VARCHAR(255) NOT NULL, stream_archive BOOLEAN NOT NULL, stream_create BOOLEAN NOT NULL, stream_roles BOOLEAN NOT NULL, stream_edit BOOLEAN NOT NULL, stream_access BOOLEAN NOT NULL, stream_invite BOOLEAN NOT NULL, stream_join BOOLEAN NOT NULL, stream_kick BOOLEAN NOT NULL, stream_write BOOLEAN NOT NULL, stream_read BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_r_stream_id ON role (stream_id)');
        $this->addSql('COMMENT ON COLUMN role.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN role.stream_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "stream" (id UUID NOT NULL, owner_id UUID DEFAULT NULL, default_user_role_id UUID DEFAULT NULL, default_creator_role_id UUID DEFAULT NULL, default_bot_role_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(512) DEFAULT NULL, discoverable BOOLEAN NOT NULL, private BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F0E9BE1CACD8D90C ON "stream" (default_user_role_id)');
        $this->addSql('CREATE INDEX IDX_F0E9BE1CC0C73304 ON "stream" (default_creator_role_id)');
        $this->addSql('CREATE INDEX IDX_F0E9BE1CFD065C3F ON "stream" (default_bot_role_id)');
        $this->addSql('CREATE INDEX idx_s_stream_owner ON "stream" (owner_id)');
        $this->addSql('CREATE INDEX idx_s_stream_owner_name ON "stream" (owner_id, name)');
        $this->addSql('CREATE INDEX idx_s_stream_owner_disc ON "stream" (owner_id, discoverable)');
        $this->addSql('CREATE INDEX idx_s_stream_owner_disc_name ON "stream" (owner_id, discoverable, name)');
        $this->addSql('COMMENT ON COLUMN "stream".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "stream".owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "stream".default_user_role_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "stream".default_creator_role_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "stream".default_bot_role_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE stream_user (id UUID NOT NULL, user_id VARCHAR(255) NOT NULL, stream_id UUID NOT NULL, last_seen_event_id UUID DEFAULT NULL, invite_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3A84EFEA832799BB ON stream_user (last_seen_event_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3A84EFEAEA417747 ON stream_user (invite_id)');
        $this->addSql('CREATE INDEX idx_su_stream_id ON stream_user (stream_id)');
        $this->addSql('CREATE INDEX idx_su_user_id ON stream_user (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_stream_user ON stream_user (user_id, stream_id)');
        $this->addSql('COMMENT ON COLUMN stream_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN stream_user.stream_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN stream_user.last_seen_event_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN stream_user.invite_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE stream_user_role (stream_user_id UUID NOT NULL, role_id UUID NOT NULL, PRIMARY KEY(stream_user_id, role_id))');
        $this->addSql('CREATE INDEX IDX_25A11944F1073866 ON stream_user_role (stream_user_id)');
        $this->addSql('CREATE INDEX IDX_25A11944D60322AC ON stream_user_role (role_id)');
        $this->addSql('COMMENT ON COLUMN stream_user_role.stream_user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN stream_user_role.role_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE subscription (id UUID NOT NULL, transport_id VARCHAR(255) DEFAULT NULL, stream_user_id UUID NOT NULL, event_types TEXT DEFAULT NULL, transport_configuration VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A3C664D39909C13F ON subscription (transport_id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3F1073866 ON subscription (stream_user_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_transport_stream_user ON subscription (transport_id, stream_user_id)');
        $this->addSql('COMMENT ON COLUMN subscription.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.stream_user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.event_types IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE transport (name VARCHAR(255) NOT NULL, return_secret VARCHAR(255) DEFAULT NULL, PRIMARY KEY(name))');
        $this->addSql('CREATE TABLE "user" (id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7D0ED463E FOREIGN KEY (stream_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7B2A20893 FOREIGN KEY (event_data_id) REFERENCES event_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA79909C13F FOREIGN KEY (transport_id) REFERENCES transport (name) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invite ADD CONSTRAINT FK_C7E210D7D0ED463E FOREIGN KEY (stream_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6AD0ED463E FOREIGN KEY (stream_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "stream" ADD CONSTRAINT FK_F0E9BE1C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "stream" ADD CONSTRAINT FK_F0E9BE1CACD8D90C FOREIGN KEY (default_user_role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "stream" ADD CONSTRAINT FK_F0E9BE1CC0C73304 FOREIGN KEY (default_creator_role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "stream" ADD CONSTRAINT FK_F0E9BE1CFD065C3F FOREIGN KEY (default_bot_role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user ADD CONSTRAINT FK_3A84EFEAA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user ADD CONSTRAINT FK_3A84EFEAD0ED463E FOREIGN KEY (stream_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user ADD CONSTRAINT FK_3A84EFEA832799BB FOREIGN KEY (last_seen_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user ADD CONSTRAINT FK_3A84EFEAEA417747 FOREIGN KEY (invite_id) REFERENCES invite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user_role ADD CONSTRAINT FK_25A11944F1073866 FOREIGN KEY (stream_user_id) REFERENCES stream_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user_role ADD CONSTRAINT FK_25A11944D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39909C13F FOREIGN KEY (transport_id) REFERENCES transport (name) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3F1073866 FOREIGN KEY (stream_user_id) REFERENCES stream_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE stream_user DROP CONSTRAINT FK_3A84EFEA832799BB');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7B2A20893');
        $this->addSql('ALTER TABLE stream_user DROP CONSTRAINT FK_3A84EFEAEA417747');
        $this->addSql('ALTER TABLE "stream" DROP CONSTRAINT FK_F0E9BE1CACD8D90C');
        $this->addSql('ALTER TABLE "stream" DROP CONSTRAINT FK_F0E9BE1CC0C73304');
        $this->addSql('ALTER TABLE "stream" DROP CONSTRAINT FK_F0E9BE1CFD065C3F');
        $this->addSql('ALTER TABLE stream_user_role DROP CONSTRAINT FK_25A11944D60322AC');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7D0ED463E');
        $this->addSql('ALTER TABLE invite DROP CONSTRAINT FK_C7E210D7D0ED463E');
        $this->addSql('ALTER TABLE role DROP CONSTRAINT FK_57698A6AD0ED463E');
        $this->addSql('ALTER TABLE "stream" DROP CONSTRAINT FK_F0E9BE1C7E3C61F9');
        $this->addSql('ALTER TABLE stream_user DROP CONSTRAINT FK_3A84EFEAD0ED463E');
        $this->addSql('ALTER TABLE stream_user_role DROP CONSTRAINT FK_25A11944F1073866');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3F1073866');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA79909C13F');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D39909C13F');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE stream_user DROP CONSTRAINT FK_3A84EFEAA76ED395');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_data');
        $this->addSql('DROP TABLE invite');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE "stream"');
        $this->addSql('DROP TABLE stream_user');
        $this->addSql('DROP TABLE stream_user_role');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE transport');
        $this->addSql('DROP TABLE "user"');
    }
}
