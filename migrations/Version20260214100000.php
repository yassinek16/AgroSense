<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Add User relationships to Serre and Zone entities
 * 
 * This migration:
 * - Adds user_id foreign key to serre table with CASCADE delete
 * - Adds user_id foreign key to zone table with CASCADE delete
 * - Makes zone.serre_id non-nullable (was previously nullable)
 */
final class Version20260214100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User relationships to Serre and Zone entities and make Zone.serre non-nullable';
    }

    public function up(Schema $schema): void
    {
        // Add user_id to serre table
        $this->addSql('ALTER TABLE serre ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE serre ADD CONSTRAINT FK_84B67E17A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_84B67E17A76ED395 ON serre (user_id)');
        
        // Add user_id to zone table
        $this->addSql('ALTER TABLE zone ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0F6F25DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_A0F6F25DA76ED395 ON zone (user_id)');
        
        // Make zone.serre_id non-nullable (currently it's nullable and we need to fix that)
        $this->addSql('ALTER TABLE zone MODIFY serre_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert zone.serre_id to nullable
        $this->addSql('ALTER TABLE zone MODIFY serre_id INT');
        
        // Remove zone.user_id foreign key
        $this->addSql('ALTER TABLE zone DROP FOREIGN KEY FK_A0F6F25DA76ED395');
        $this->addSql('DROP INDEX IDX_A0F6F25DA76ED395 ON zone');
        $this->addSql('ALTER TABLE zone DROP COLUMN user_id');
        
        // Remove serre.user_id foreign key
        $this->addSql('ALTER TABLE serre DROP FOREIGN KEY FK_84B67E17A76ED395');
        $this->addSql('DROP INDEX IDX_84B67E17A76ED395 ON serre');
        $this->addSql('ALTER TABLE serre DROP COLUMN user_id');
    }
}
