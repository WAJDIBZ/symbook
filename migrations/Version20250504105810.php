<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504105810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration vers le système d\'utilisateurs unifié avec User et rôles';
    }

    public function up(Schema $schema): void
    {
        // Étape 1: Créer la table user si elle n'existe pas déjà
        if (!$schema->hasTable('user')) {
            $this->addSql('CREATE TABLE `user` (
                id INT AUTO_INCREMENT NOT NULL,
                email VARCHAR(180) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                nom VARCHAR(255) NOT NULL,
                adresse VARCHAR(255) DEFAULT NULL,
                date_creation DATE NOT NULL,
                UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Étape 2: Migrer les données des clients vers la table user
        $this->addSql('INSERT INTO `user` (email, roles, password, nom, adresse, date_creation)
            SELECT u.email, JSON_ARRAY("ROLE_USER"), u.mot_de_passe, c.nom, c.adresse, u.date_creation
            FROM client c
            JOIN utilisateur u ON c.id = u.id
            WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE email = u.email)');

        // Étape 3: Migrer les données des administrateurs vers la table user
        $this->addSql('INSERT INTO `user` (email, roles, password, nom, adresse, date_creation)
            SELECT u.email, JSON_ARRAY("ROLE_ADMIN"), u.mot_de_passe, u.email as nom, NULL, u.date_creation
            FROM administrateur a
            JOIN utilisateur u ON a.id = u.id
            WHERE NOT EXISTS (SELECT 1 FROM `user` WHERE email = u.email)');

        // Étape 4: Mettre à jour la table commande pour faire référence aux nouveaux IDs d'utilisateurs
        $this->addSql('
            UPDATE commande c
            JOIN client cl ON c.client_id = cl.id
            JOIN utilisateur u ON cl.id = u.id
            JOIN `user` nu ON u.email = nu.email
            SET c.client_id = nu.id
        ');

        // Étape 5: Maintenant, nous pouvons ajouter la contrainte de clé étrangère
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // Supprimer d'abord la contrainte de clé étrangère
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');

        // Option: supprimer la table user si nécessaire
        // $this->addSql('DROP TABLE `user`');
    }
}
