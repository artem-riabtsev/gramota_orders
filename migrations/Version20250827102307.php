<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827102307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE order_total order_total BIGINT NOT NULL, CHANGE total_paid total_paid BIGINT NOT NULL');
        $this->addSql('ALTER TABLE order_item CHANGE price price BIGINT NOT NULL, CHANGE line_total line_total BIGINT NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE amount amount BIGINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE order_total order_total NUMERIC(10, 2) NOT NULL, CHANGE total_paid total_paid NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE order_item CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE line_total line_total NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE amount amount NUMERIC(10, 2) NOT NULL');
    }
}
