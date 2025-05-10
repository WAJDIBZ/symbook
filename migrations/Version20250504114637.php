<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504114637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la colonne user_id existe déjà
        $columns = $this->connection->fetchAllAssociative('SHOW COLUMNS FROM commande');
        $columnExists = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'user_id') {
                $columnExists = true;
                break;
            }
        }

        // 1. Ajouter la colonne user_id seulement si elle n'existe pas
        if (!$columnExists) {
            $this->addSql('ALTER TABLE commande ADD user_id INT NULL');
        }

        // 2. Vérifier si un utilisateur existe, sinon en créer un par défaut
        $this->addSql("INSERT INTO `user` (email, roles, mot_de_passe, nom, prenom, date_creation)
                      SELECT 'admin@example.com', '[\"ROLE_ADMIN\"]', '" . password_hash('admin123', PASSWORD_BCRYPT) . "', 'Admin', 'System', NOW()
                      FROM dual
                      WHERE NOT EXISTS (SELECT * FROM `user` LIMIT 1)");

        // 3. Récupérer l'ID du premier utilisateur pour l'utiliser comme référence
        $this->addSql("SET @default_user_id = (SELECT id FROM `user` LIMIT 1)");

        // 4. Mettre à jour toutes les commandes existantes pour utiliser cet utilisateur par défaut
        $this->addSql("UPDATE commande SET user_id = @default_user_id WHERE user_id IS NULL");

        // 5. Maintenant que toutes les commandes ont un user_id, ajouter la contrainte NOT NULL si nécessaire
        // Vérifier d'abord si la colonne est déjà NOT NULL
        $columns = $this->connection->fetchAllAssociative('SHOW COLUMNS FROM commande WHERE Field = "user_id"');
        if (count($columns) > 0 && $columns[0]['Null'] === 'YES') {
            $this->addSql('ALTER TABLE commande MODIFY user_id INT NOT NULL');
        }

        // 6. Vérifier si la contrainte de clé étrangère existe déjà
        $foreignKeys = $this->connection->fetchAllAssociative('
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = "commande"
            AND COLUMN_NAME = "user_id"
            AND REFERENCED_TABLE_NAME = "user"
            AND CONSTRAINT_SCHEMA = DATABASE()
        ');

        if (count($foreignKeys) === 0) {
            // Ajouter la contrainte de clé étrangère seulement si elle n'existe pas
            $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');

            // Vérifier si l'index existe déjà
            $indexes = $this->connection->fetchAllAssociative('SHOW INDEX FROM commande WHERE Column_name = "user_id"');
            if (count($indexes) === 0) {
                $this->addSql('CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)');
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('DROP INDEX IDX_6EEAA67DA76ED395 ON commande');
        $this->addSql('ALTER TABLE commande DROP user_id');
    }
}
