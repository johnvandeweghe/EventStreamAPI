<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200616172936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE webhook_subscription_data (id UUID NOT NULL, uri TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN webhook_subscription_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE subscription ADD webhook_data_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN subscription.webhook_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D31968FFA4 FOREIGN KEY (webhook_data_id) REFERENCES webhook_subscription_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D31968FFA4 ON subscription (webhook_data_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D31968FFA4');
        $this->addSql('DROP TABLE webhook_subscription_data');
        $this->addSql('DROP INDEX UNIQ_A3C664D31968FFA4');
        $this->addSql('ALTER TABLE subscription DROP webhook_data_id');
    }
}
