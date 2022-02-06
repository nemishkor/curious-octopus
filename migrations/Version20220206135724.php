<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220206135724 extends AbstractMigration {

    public function getDescription(): string {
        return '';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8A2BF053A');
        $this->addSql('ALTER TABLE job CHANGE db_id db_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8A2BF053A FOREIGN KEY (db_id) REFERENCES `database` (id) ON DELETE SET NULL'
        );
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8A2BF053A');
        $this->addSql('ALTER TABLE job CHANGE db_id db_id INT NOT NULL');
        $this->addSql(
            'ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8A2BF053A FOREIGN KEY (db_id) REFERENCES `database` (id)'
        );
    }

}
