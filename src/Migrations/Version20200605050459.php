<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200605050459 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE subscription (id UUID NOT NULL, group_member_id UUID NOT NULL, transport VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A3C664D3B5248F1F ON subscription (group_member_id)');
        $this->addSql('COMMENT ON COLUMN subscription.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.group_member_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE event (id UUID NOT NULL, user_id VARCHAR(255) NOT NULL, event_group_id UUID NOT NULL, message_event_data_id UUID DEFAULT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7A76ED395 ON event (user_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7B8B83097 ON event (event_group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7FB0F4EEB ON event (message_event_data_id)');
        $this->addSql('CREATE INDEX group_datetime ON event (event_group_id, datetime)');
        $this->addSql('COMMENT ON COLUMN event.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.event_group_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.message_event_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN event.datetime IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE group_member (id UUID NOT NULL, user_id VARCHAR(255) NOT NULL, user_group_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A36222A8A76ED395 ON group_member (user_id)');
        $this->addSql('CREATE INDEX IDX_A36222A81ED93D47 ON group_member (user_group_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_groupmembership ON group_member (user_id, user_group_id)');
        $this->addSql('COMMENT ON COLUMN group_member.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN group_member.user_group_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, nickname VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "group" (id UUID NOT NULL, owner_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, discoverable BOOLEAN NOT NULL, private BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6DC044C57E3C61F9 ON "group" (owner_id)');
        $this->addSql('COMMENT ON COLUMN "group".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "group".owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE message_event_data (id UUID NOT NULL, text TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN message_event_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3B5248F1F FOREIGN KEY (group_member_id) REFERENCES group_member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7B8B83097 FOREIGN KEY (event_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7FB0F4EEB FOREIGN KEY (message_event_data_id) REFERENCES message_event_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_member ADD CONSTRAINT FK_A36222A8A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_member ADD CONSTRAINT FK_A36222A81ED93D47 FOREIGN KEY (user_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "group" ADD CONSTRAINT FK_6DC044C57E3C61F9 FOREIGN KEY (owner_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3B5248F1F');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE group_member DROP CONSTRAINT FK_A36222A8A76ED395');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7B8B83097');
        $this->addSql('ALTER TABLE group_member DROP CONSTRAINT FK_A36222A81ED93D47');
        $this->addSql('ALTER TABLE "group" DROP CONSTRAINT FK_6DC044C57E3C61F9');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7FB0F4EEB');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE group_member');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE message_event_data');
    }
}
