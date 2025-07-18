<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718072923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) NOT NULL, line_total NUMERIC(10, 2) NOT NULL, description INT NOT NULL, order_id INT DEFAULT NULL, product_id INT NOT NULL, INDEX IDX_52EA1F098D9F6D38 (order_id), INDEX IDX_52EA1F094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart DROP FOREIGN KEY FK_BA388B74584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart DROP FOREIGN KEY FK_BA388B78D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE price
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cart
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE amount order_total NUMERIC(10, 2) NOT NULL, CHANGE payment_amount total_paid NUMERIC(10, 2) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE price (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, price NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, order_id INT DEFAULT NULL, product_id INT NOT NULL, INDEX IDX_BA388B78D9F6D38 (order_id), INDEX IDX_BA388B74584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD CONSTRAINT FK_BA388B74584665A FOREIGN KEY (product_id) REFERENCES price (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cart ADD CONSTRAINT FK_BA388B78D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE order_total amount NUMERIC(10, 2) NOT NULL, CHANGE total_paid payment_amount NUMERIC(10, 2) DEFAULT NULL
        SQL);
    }
}
