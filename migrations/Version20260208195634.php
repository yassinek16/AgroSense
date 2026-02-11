<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208195634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE serre (id INT AUTO_INCREMENT NOT NULL, nom_serre VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, surface DOUBLE PRECISION NOT NULL, etat_serre VARCHAR(255) NOT NULL, date_mise_en_service DATE NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, nom_zone VARCHAR(255) NOT NULL, type_zone VARCHAR(255) NOT NULL, superficie DOUBLE PRECISION NOT NULL, etat_zone VARCHAR(255) NOT NULL, culture_associee VARCHAR(255) NOT NULL, serre_id INT NOT NULL, INDEX IDX_A0EBC007CE732E2E (serre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE zone ADD CONSTRAINT FK_A0EBC007CE732E2E FOREIGN KEY (serre_id) REFERENCES serre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE zone DROP FOREIGN KEY FK_A0EBC007CE732E2E');
        $this->addSql('DROP TABLE serre');
        $this->addSql('DROP TABLE zone');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
