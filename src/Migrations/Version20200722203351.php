<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200722203351 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX idx_group_user_datetime ON event (event_group_id, user_id, datetime)');
        $this->addSql('ALTER INDEX group_datetime RENAME TO idx_group_datetime');
        $this->addSql('ALTER INDEX idx_a36222a81ed93d47 RENAME TO idx_group_id');
        $this->addSql('ALTER INDEX idx_a36222a8a76ed395 RENAME TO idx_user_id');
        $this->addSql('CREATE INDEX idx_group_owner_name ON "group" (owner_id, name)');
        $this->addSql('CREATE INDEX idx_group_owner_disc ON "group" (owner_id, discoverable)');
        $this->addSql('CREATE INDEX idx_group_owner_disc_name ON "group" (owner_id, discoverable, name)');
        $this->addSql('ALTER INDEX idx_6dc044c57e3c61f9 RENAME TO idx_group_owner');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX idx_group_user_datetime');
        $this->addSql('ALTER INDEX idx_group_datetime RENAME TO group_datetime');
        $this->addSql('ALTER INDEX idx_group_id RENAME TO idx_a36222a81ed93d47');
        $this->addSql('ALTER INDEX idx_user_id RENAME TO idx_a36222a8a76ed395');
        $this->addSql('DROP INDEX idx_group_owner_name');
        $this->addSql('DROP INDEX idx_group_owner_disc');
        $this->addSql('DROP INDEX idx_group_owner_disc_name');
        $this->addSql('ALTER INDEX idx_group_owner RENAME TO idx_6dc044c57e3c61f9');
    }
}
