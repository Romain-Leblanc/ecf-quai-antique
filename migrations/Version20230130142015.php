<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230130142015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, fk_utilisateur_id INT DEFAULT NULL, fk_visiteur_id INT DEFAULT NULL, date DATE NOT NULL, heure TIME NOT NULL, INDEX IDX_42C849558E8608A6 (fk_utilisateur_id), INDEX IDX_42C8495516849F09 (fk_visiteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visiteur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, email VARCHAR(180) NOT NULL, nombre_convives INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849558E8608A6 FOREIGN KEY (fk_utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495516849F09 FOREIGN KEY (fk_visiteur_id) REFERENCES visiteur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849558E8608A6');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495516849F09');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE visiteur');
    }
}
