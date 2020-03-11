<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190521133347 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE telegram_message_log ADD user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram_message_log ADD CONSTRAINT FK_16886ABAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_16886ABAA76ED395 ON telegram_message_log (user_id)');
        $this->addSql('UPDATE telegram_message_log INNER JOIN `user` ON telegram_message_log.`chat_id` = `user`.telegram_ref SET telegram_message_log.`user_id` = `user`.`id`');
        $this->addSql('ALTER TABLE telegram_message_log DROP `chat_id`');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `telegram_message_log` DROP FOREIGN KEY FK_16886ABAA76ED395');
        $this->addSql('DROP INDEX IDX_16886ABAA76ED395 ON `telegram_message_log`');
        $this->addSql('ALTER TABLE `telegram_message_log` ADD chat_id INT NOT NULL, DROP user_id');
    }
}
