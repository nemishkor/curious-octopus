<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220130113511 extends AbstractMigration {

    public function getDescription(): string {
        return '';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE job (id INT AUTO_INCREMENT NOT NULL, query_id INT NOT NULL, db_id INT NOT NULL, state VARCHAR(255) NOT NULL, result LONGTEXT DEFAULT NULL, error LONGTEXT DEFAULT NULL, INDEX IDX_FBD8E0F8EF946F99 (query_id), INDEX IDX_FBD8E0F8A2BF053A (db_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8EF946F99 FOREIGN KEY (query_id) REFERENCES query (id)'
        );
        $this->addSql(
            'ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8A2BF053A FOREIGN KEY (db_id) REFERENCES `database` (id)'
        );
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE job');
    }

}
