<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220127235044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_user_group (group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_48F3FA3BFE54D947 (group_id), INDEX IDX_48F3FA3BA76ED395 (user_id), PRIMARY KEY(group_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, app_user_id INT NOT NULL, message VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL, INDEX IDX_FAB3FC164A3353D8 (app_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_group_message (message_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_AAFC3CAC537A1329 (message_id), INDEX IDX_AAFC3CACFE54D947 (group_id), PRIMARY KEY(message_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, pp VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat_user_group ADD CONSTRAINT FK_48F3FA3BFE54D947 FOREIGN KEY (group_id) REFERENCES chat_group (id)');
        $this->addSql('ALTER TABLE chat_user_group ADD CONSTRAINT FK_48F3FA3BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC164A3353D8 FOREIGN KEY (app_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_group_message ADD CONSTRAINT FK_AAFC3CAC537A1329 FOREIGN KEY (message_id) REFERENCES chat_message (id)');
        $this->addSql('ALTER TABLE chat_group_message ADD CONSTRAINT FK_AAFC3CACFE54D947 FOREIGN KEY (group_id) REFERENCES chat_group (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_user_group DROP FOREIGN KEY FK_48F3FA3BFE54D947');
        $this->addSql('ALTER TABLE chat_group_message DROP FOREIGN KEY FK_AAFC3CACFE54D947');
        $this->addSql('ALTER TABLE chat_group_message DROP FOREIGN KEY FK_AAFC3CAC537A1329');
        $this->addSql('ALTER TABLE chat_user_group DROP FOREIGN KEY FK_48F3FA3BA76ED395');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC164A3353D8');
        $this->addSql('DROP TABLE chat_group');
        $this->addSql('DROP TABLE chat_user_group');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE chat_group_message');
        $this->addSql('DROP TABLE user');
    }
}
