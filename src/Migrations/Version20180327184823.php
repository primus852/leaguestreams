<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180327184823 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE fos_user');
        $this->addSql('DROP TABLE ls_masteries');
        $this->addSql('DROP TABLE ls_runes');
        $this->addSql('DROP TABLE reddit_top');
        $this->addSql('ALTER TABLE ls_perks CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE ls_platforms CHANGE channelurl channel_url VARCHAR(200) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, username_canonical VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, email VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, email_canonical VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, password VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL COLLATE utf8_unicode_ci, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_masteries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, description VARCHAR(500) NOT NULL COLLATE utf8_unicode_ci, last_modified DATETIME NOT NULL, image VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, offset INT NOT NULL, type VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, row INT NOT NULL, pos VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_runes (id INT NOT NULL, name VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, description VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, last_modified DATETIME NOT NULL, image VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_8E02A046BF396750 (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reddit_top (id INT AUTO_INCREMENT NOT NULL, title LONGTEXT NOT NULL COLLATE utf8_unicode_ci, reddit_id VARCHAR(150) NOT NULL COLLATE utf8_unicode_ci, created DATETIME NOT NULL, comments INT NOT NULL, ups INT NOT NULL, score INT NOT NULL, sub_reddit VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ls_perks CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE ls_platforms CHANGE channel_url channelurl VARCHAR(200) NOT NULL COLLATE utf8_unicode_ci');
    }
}
