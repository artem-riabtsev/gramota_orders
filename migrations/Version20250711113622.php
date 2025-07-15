<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250711113622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD product_id INT NOT NULL, DROP name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD CONSTRAINT FK_BA388B74584665A FOREIGN KEY (product_id) REFERENCES price (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BA388B74584665A ON cart (product_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart DROP FOREIGN KEY FK_BA388B74584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BA388B74584665A ON cart
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD name VARCHAR(255) NOT NULL, DROP product_id
        SQL);
    }
}
