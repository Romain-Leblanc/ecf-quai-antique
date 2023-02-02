<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230202194543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergie_utilisateur (id INT AUTO_INCREMENT NOT NULL, fk_utilisateur_id INT NOT NULL, allergie VARCHAR(50) NOT NULL, INDEX IDX_3A5002D08E8608A6 (fk_utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE allergie_visiteur (id INT AUTO_INCREMENT NOT NULL, fk_visiteur_id INT NOT NULL, allergie VARCHAR(50) NOT NULL, INDEX IDX_B023C34B16849F09 (fk_visiteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE allergie_utilisateur ADD CONSTRAINT FK_3A5002D08E8608A6 FOREIGN KEY (fk_utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE allergie_visiteur ADD CONSTRAINT FK_B023C34B16849F09 FOREIGN KEY (fk_visiteur_id) REFERENCES visiteur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allergie_utilisateur DROP FOREIGN KEY FK_3A5002D08E8608A6');
        $this->addSql('ALTER TABLE allergie_visiteur DROP FOREIGN KEY FK_B023C34B16849F09');
        $this->addSql('DROP TABLE allergie_utilisateur');
        $this->addSql('DROP TABLE allergie_visiteur');
    }
}
