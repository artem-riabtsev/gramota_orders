<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250711123806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, amount NUMERIC(10, 2) NOT NULL, order_id INT DEFAULT NULL, INDEX IDX_6D28840D8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D8D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment
        SQL);
    }
}
