<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210307222009 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE transport (name VARCHAR(255) NOT NULL, two_way BOOLEAN NOT NULL, PRIMARY KEY(name))');
        $this->addSql('ALTER TABLE event ADD transport_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA79909C13F FOREIGN KEY (transport_id) REFERENCES transport (name) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA79909C13F ON event (transport_id)');
        $this->addSql('CREATE INDEX idx_e_stream_transport_datetime ON event (stream_id, transport_id, datetime)');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT fk_a3c664d31968ffa4');
        $this->addSql('DROP INDEX uniq_a3c664d31968ffa4');
        $this->addSql('DROP INDEX uq_transport_stream_user');
        $this->addSql('ALTER TABLE subscription ADD transport_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription ADD transport_configuration VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription DROP webhook_data_id');
        $this->addSql('ALTER TABLE subscription DROP transport');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39909C13F FOREIGN KEY (transport_id) REFERENCES transport (name) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A3C664D39909C13F ON subscription (transport_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_transport_stream_user ON subscription (transport_id, stream_user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA79909C13F');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D39909C13F');
        $this->addSql('DROP TABLE transport');
        $this->addSql('DROP INDEX IDX_3BAE0AA79909C13F');
        $this->addSql('DROP INDEX idx_e_stream_transport_datetime');
        $this->addSql('ALTER TABLE event DROP transport_id');
        $this->addSql('DROP INDEX IDX_A3C664D39909C13F');
        $this->addSql('DROP INDEX uq_transport_stream_user');
        $this->addSql('ALTER TABLE subscription ADD webhook_data_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription ADD transport VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE subscription DROP transport_id');
        $this->addSql('ALTER TABLE subscription DROP transport_configuration');
        $this->addSql('COMMENT ON COLUMN subscription.webhook_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_a3c664d31968ffa4 FOREIGN KEY (webhook_data_id) REFERENCES webhook_subscription_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_a3c664d31968ffa4 ON subscription (webhook_data_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_transport_stream_user ON subscription (transport, stream_user_id)');
    }
}
