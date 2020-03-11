<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190420123139 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_search (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, rss_link VARCHAR(500) NOT NULL, search_url VARCHAR(500) NOT NULL, is_pending TINYINT(1) NOT NULL, processed_json LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_D1A2C09AA76ED395 (user_id), UNIQUE INDEX unique_user_link (user_id, rss_link), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_search ADD CONSTRAINT FK_D1A2C09AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE user_rss_settings');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_rss_settings (id VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, user_id VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, rss_link VARCHAR(500) NOT NULL COLLATE utf8mb4_unicode_ci, processed_json LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, is_pending TINYINT(1) NOT NULL, search_url VARCHAR(500) NOT NULL COLLATE utf8mb4_unicode_ci, UNIQUE INDEX unique_user_link (user_id, rss_link), INDEX IDX_645A5E64A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_rss_settings ADD CONSTRAINT FK_645A5E64A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE user_search');
    }
}
