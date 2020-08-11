<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200811172446 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE stream_user (id UUID NOT NULL, user_id VARCHAR(255) NOT NULL, stream_id UUID NOT NULL, last_seen_event_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3A84EFEA832799BB ON stream_user (last_seen_event_id)');
        $this->addSql('CREATE INDEX idx_stream_id ON stream_user (stream_id)');
        $this->addSql('CREATE INDEX idx_user_id ON stream_user (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_stream_user ON stream_user (user_id, stream_id)');
        $this->addSql('COMMENT ON COLUMN stream_user.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN stream_user.stream_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN stream_user.last_seen_event_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE subscription (id UUID NOT NULL, stream_user_id UUID NOT NULL, webhook_data_id UUID DEFAULT NULL, transport VARCHAR(255) NOT NULL, event_types TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A3C664D3F1073866 ON subscription (stream_user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D31968FFA4 ON subscription (webhook_data_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_transport_stream_user ON subscription (transport, stream_user_id)');
        $this->addSql('COMMENT ON COLUMN subscription.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.stream_user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.webhook_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.event_types IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE command_event_data (id UUID NOT NULL, command VARCHAR(255) NOT NULL, parameters JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN command_event_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE message_event_data (id UUID NOT NULL, text TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN message_event_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE event (id UUID NOT NULL, user_id VARCHAR(255) NOT NULL, stream_id UUID NOT NULL, message_event_data_id UUID DEFAULT NULL, command_event_data_id UUID DEFAULT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7A76ED395 ON event (user_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7D0ED463E ON event (stream_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7FB0F4EEB ON event (message_event_data_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7ABE4545E ON event (command_event_data_id)');
        $this->addSql('CREATE INDEX idx_stream_datetime ON event (stream_id, datetime)');
        $this->addSql('CREATE INDEX idx_stream_user_datetime ON event (stream_id, user_id, datetime)');
        $this->addSql('COMMENT ON COLUMN event.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.stream_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.message_event_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.command_event_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.datetime IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE webhook_subscription_data (id UUID NOT NULL, uri TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN webhook_subscription_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, nickname VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "stream" (id UUID NOT NULL, owner_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(512) DEFAULT NULL, discoverable BOOLEAN NOT NULL, private BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_stream_owner ON "stream" (owner_id)');
        $this->addSql('CREATE INDEX idx_stream_owner_name ON "stream" (owner_id, name)');
        $this->addSql('CREATE INDEX idx_stream_owner_disc ON "stream" (owner_id, discoverable)');
        $this->addSql('CREATE INDEX idx_stream_owner_disc_name ON "stream" (owner_id, discoverable, name)');
        $this->addSql('COMMENT ON COLUMN "stream".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "stream".owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE stream_user ADD CONSTRAINT FK_3A84EFEAA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user ADD CONSTRAINT FK_3A84EFEAD0ED463E FOREIGN KEY (stream_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stream_user ADD CONSTRAINT FK_3A84EFEA832799BB FOREIGN KEY (last_seen_event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3F1073866 FOREIGN KEY (stream_user_id) REFERENCES stream_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D31968FFA4 FOREIGN KEY (webhook_data_id) REFERENCES webhook_subscription_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7D0ED463E FOREIGN KEY (stream_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7FB0F4EEB FOREIGN KEY (message_event_data_id) REFERENCES message_event_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7ABE4545E FOREIGN KEY (command_event_data_id) REFERENCES command_event_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "stream" ADD CONSTRAINT FK_F0E9BE1C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "stream" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3F1073866');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7ABE4545E');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7FB0F4EEB');
        $this->addSql('ALTER TABLE stream_user DROP CONSTRAINT FK_3A84EFEA832799BB');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D31968FFA4');
        $this->addSql('ALTER TABLE stream_user DROP CONSTRAINT FK_3A84EFEAA76ED395');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE stream_user DROP CONSTRAINT FK_3A84EFEAD0ED463E');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7D0ED463E');
        $this->addSql('ALTER TABLE "stream" DROP CONSTRAINT FK_F0E9BE1C7E3C61F9');
        $this->addSql('DROP TABLE stream_user');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE command_event_data');
        $this->addSql('DROP TABLE message_event_data');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE webhook_subscription_data');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE "stream"');
    }
}
