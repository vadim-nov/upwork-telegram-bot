<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190503043059 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_user_link ON user_search');
        $this->addSql('ALTER TABLE user_search CHANGE rss_link rss_link LONGTEXT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_user_link ON user_search (user_id, search_url)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_user_link ON user_search');
        $this->addSql('ALTER TABLE user_search CHANGE rss_link rss_link VARCHAR(500) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX unique_user_link ON user_search (user_id, rss_link)');
    }
}
