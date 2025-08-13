<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611194330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD order_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD CONSTRAINT FK_BA388B78D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BA388B78D9F6D38 ON cart (order_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD customer_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F52993989395C3F3 ON `order` (customer_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989395C3F3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F52993989395C3F3 ON `order`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP customer_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart DROP FOREIGN KEY FK_BA388B78D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BA388B78D9F6D38 ON cart
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart DROP order_id
        SQL);
    }
}
