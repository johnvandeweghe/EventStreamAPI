<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200626214145 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE command_event_data (id UUID NOT NULL, command VARCHAR(255) NOT NULL, parameters JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN command_event_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE event ADD command_event_data_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN event.command_event_data_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7ABE4545E FOREIGN KEY (command_event_data_id) REFERENCES command_event_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7ABE4545E ON event (command_event_data_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7ABE4545E');
        $this->addSql('DROP TABLE command_event_data');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7ABE4545E');
        $this->addSql('ALTER TABLE event DROP command_event_data_id');
    }
}
