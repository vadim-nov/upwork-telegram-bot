<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190417225744 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `order` (id VARCHAR(255) NOT NULL, plan_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, payment_date DATETIME DEFAULT NULL, payment_amount VARCHAR(255) DEFAULT NULL, payment_currency_code VARCHAR(3) DEFAULT NULL, INDEX IDX_F5299398E899029B (plan_id), INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plan (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, search_count VARCHAR(255) NOT NULL, update_frequency INT NOT NULL, price_amount VARCHAR(255) DEFAULT NULL, price_currency_code VARCHAR(3) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398E899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

        $this->addSql('INSERT INTO plan (
            id,
            name,
            price_amount,
            price_currency_code,
            search_count,
            update_frequency)
            VALUES
            ("1", "Starter", "5", "USD", 1, 10),
            ("2", "Standard", "10", "USD", 2, 5),
            ("3", "Premium", "15", "USD", 3, 1)
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398E899029B');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE plan');
    }
}
