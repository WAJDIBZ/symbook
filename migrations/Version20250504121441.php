<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504121441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la contrainte existe déjà
        $constraintExists = $this->connection->executeQuery("
            SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'commande' 
            AND CONSTRAINT_NAME = 'FK_6EEAA67DA76ED395'
        ")->fetchOne();

        if (!$constraintExists) {
            $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        }

        // Vérifier si l'index existe déjà
        $indexExists = $this->connection->executeQuery("
            SELECT COUNT(*) FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'commande'
            AND INDEX_NAME = 'IDX_6EEAA67DA76ED395'
        ")->fetchOne();

        if (!$indexExists) {
            $this->addSql('CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('DROP INDEX IDX_6EEAA67DA76ED395 ON commande');
    }
}
