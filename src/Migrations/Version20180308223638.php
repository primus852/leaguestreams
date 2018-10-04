<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180308223638 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ls_champions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, title VARCHAR(150) NOT NULL, image VARCHAR(150) NOT NULL, `key` VARCHAR(100) NOT NULL, blurb LONGTEXT NOT NULL, lore LONGTEXT NOT NULL, spell_passive_image VARCHAR(50) NOT NULL, spell_passive_name VARCHAR(50) NOT NULL, spell_passive_description LONGTEXT NOT NULL, spell_qimage VARCHAR(50) NOT NULL, spell_qname VARCHAR(50) NOT NULL, spell_qdescription LONGTEXT NOT NULL, spell_wimage VARCHAR(50) NOT NULL, spell_wname VARCHAR(50) NOT NULL, spell_wdescription LONGTEXT NOT NULL, spell_eimage VARCHAR(50) NOT NULL, spell_ename VARCHAR(50) NOT NULL, spell_edescription LONGTEXT NOT NULL, spell_rimage VARCHAR(50) NOT NULL, spell_rname VARCHAR(50) NOT NULL, spell_rdescription LONGTEXT NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_current_match (id INT AUTO_INCREMENT NOT NULL, champion_id INT DEFAULT NULL, map_id INT DEFAULT NULL, summoner_id INT DEFAULT NULL, queue_id INT DEFAULT NULL, p1_spell1_id INT DEFAULT NULL, p1_spell2_id INT DEFAULT NULL, match_id BIGINT NOT NULL, is_playing TINYINT(1) NOT NULL, team INT NOT NULL, length INT NOT NULL, type VARCHAR(50) NOT NULL, mode VARCHAR(50) NOT NULL, runes LONGTEXT DEFAULT NULL, masteries LONGTEXT DEFAULT NULL, perks LONGTEXT DEFAULT NULL, modified DATETIME NOT NULL, INDEX IDX_C5572E27FA7FD7EB (champion_id), INDEX IDX_C5572E2753C55F64 (map_id), UNIQUE INDEX UNIQ_C5572E27BC01C675 (summoner_id), INDEX IDX_C5572E27477B5BAE (queue_id), INDEX IDX_C5572E27B0F7D5CB (p1_spell1_id), INDEX IDX_C5572E27A2427A25 (p1_spell2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_live (id INT AUTO_INCREMENT NOT NULL, count INT NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_maps (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_matches (id INT AUTO_INCREMENT NOT NULL, streamer INT DEFAULT NULL, region INT DEFAULT NULL, champion INT DEFAULT NULL, enemy_champion_id INT DEFAULT NULL, summoner INT DEFAULT NULL, map INT DEFAULT NULL, queue_id INT DEFAULT NULL, p1_spell1_id INT DEFAULT NULL, p1_spell2_id INT DEFAULT NULL, match_id BIGINT NOT NULL, team INT NOT NULL, crawled TINYINT(1) NOT NULL, lane VARCHAR(25) NOT NULL, role VARCHAR(25) NOT NULL, length INT NOT NULL, type VARCHAR(50) NOT NULL, win INT NOT NULL, game_creation BIGINT DEFAULT NULL, game_version VARCHAR(50) DEFAULT NULL, runes LONGTEXT DEFAULT NULL, masteries LONGTEXT DEFAULT NULL, perks LONGTEXT DEFAULT NULL, modified DATETIME NOT NULL, INDEX IDX_F63358362DF6AE32 (streamer), INDEX IDX_F6335836F62F176 (region), INDEX IDX_F633583645437EB4 (champion), INDEX IDX_F6335836FF8FE01F (enemy_champion_id), INDEX IDX_F6335836ABE89763 (summoner), INDEX IDX_F633583693ADAABB (map), INDEX IDX_F6335836477B5BAE (queue_id), INDEX IDX_F6335836B0F7D5CB (p1_spell1_id), INDEX IDX_F6335836A2427A25 (p1_spell2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_perks (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_platforms (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, login_url VARCHAR(100) DEFAULT NULL, url VARCHAR(200) NOT NULL, channel_url VARCHAR(200) NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_queues (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_regions (id INT AUTO_INCREMENT NOT NULL, short VARCHAR(10) NOT NULL, `long` VARCHAR(15) NOT NULL, url VARCHAR(150) NOT NULL, port INT NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_reports (id INT AUTO_INCREMENT NOT NULL, streamer_id INT DEFAULT NULL, region_id INT DEFAULT NULL, description LONGTEXT NOT NULL, ip VARCHAR(100) NOT NULL, resolved INT NOT NULL, last_modified DATETIME NOT NULL, INDEX IDX_10AEAC925F432AD (streamer_id), INDEX IDX_10AEAC998260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_smurf (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, streamer_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, ip VARCHAR(100) NOT NULL, last_modified DATETIME NOT NULL, INDEX IDX_5EE2DA6A98260155 (region_id), INDEX IDX_5EE2DA6A25F432AD (streamer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_spells (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, image VARCHAR(150) NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_streamer (id INT AUTO_INCREMENT NOT NULL, platform INT DEFAULT NULL, channel_name VARCHAR(100) NOT NULL, channel_user VARCHAR(150) NOT NULL, is_online TINYINT(1) NOT NULL, total_online INT NOT NULL, viewers INT DEFAULT NULL, resolution INT DEFAULT NULL, fps INT DEFAULT NULL, delay INT DEFAULT NULL, description VARCHAR(200) DEFAULT NULL, language VARCHAR(5) DEFAULT NULL, thumbnail VARCHAR(250) DEFAULT NULL, logo VARCHAR(250) DEFAULT NULL, banner VARCHAR(250) DEFAULT NULL, started DATETIME DEFAULT NULL, is_featured TINYINT(1) NOT NULL, is_partner TINYINT(1) NOT NULL, channel_id VARCHAR(50) DEFAULT NULL, modified DATETIME NOT NULL, created DATETIME NOT NULL, INDEX IDX_C90874743952D0CB (platform), UNIQUE INDEX channel_platform (channel_name, platform), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_streamer_report (id INT AUTO_INCREMENT NOT NULL, streamer_id INT DEFAULT NULL, reason LONGTEXT NOT NULL, ip VARCHAR(100) NOT NULL, is_resolved TINYINT(1) NOT NULL, INDEX IDX_8EBDC1A525F432AD (streamer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_summoner (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, streamer_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, summoner_id INT NOT NULL, division VARCHAR(10) NOT NULL, lp INT NOT NULL, league VARCHAR(15) NOT NULL, account_id VARCHAR(25) DEFAULT NULL, last_modified DATETIME NOT NULL, INDEX IDX_4F164D2598260155 (region_id), INDEX IDX_4F164D2525F432AD (streamer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spell_summoner (summoner_id INT NOT NULL, spell_id INT NOT NULL, INDEX IDX_BF84AE69BC01C675 (summoner_id), INDEX IDX_BF84AE69479EC90D (spell_id), PRIMARY KEY(summoner_id, spell_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_summoner_report (id INT AUTO_INCREMENT NOT NULL, streamer_id INT DEFAULT NULL, summoner_id INT DEFAULT NULL, reason LONGTEXT NOT NULL, ip VARCHAR(100) NOT NULL, is_resolved TINYINT(1) NOT NULL, INDEX IDX_3F88B5C725F432AD (streamer_id), INDEX IDX_3F88B5C7BC01C675 (summoner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_versions_all (version VARCHAR(15) NOT NULL, major VARCHAR(15) NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(version, major)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_versions (id INT AUTO_INCREMENT NOT NULL, version VARCHAR(15) NOT NULL, cdn VARCHAR(150) NOT NULL, champion VARCHAR(15) NOT NULL, profileicon VARCHAR(15) NOT NULL, item VARCHAR(15) NOT NULL, map VARCHAR(15) NOT NULL, mastery VARCHAR(15) NOT NULL, spell VARCHAR(15) NOT NULL, rune VARCHAR(15) NOT NULL, last_modified DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ls_vods (video_id VARCHAR(250) NOT NULL, streamer_id INT DEFAULT NULL, thumbnail VARCHAR(250) NOT NULL, created VARCHAR(50) NOT NULL, last_check DATETIME NOT NULL, length INT NOT NULL, INDEX IDX_23CD23E225F432AD (streamer_id), PRIMARY KEY(video_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ls_current_match ADD CONSTRAINT FK_C5572E27FA7FD7EB FOREIGN KEY (champion_id) REFERENCES ls_champions_legacy (id)');
        $this->addSql('ALTER TABLE ls_current_match ADD CONSTRAINT FK_C5572E2753C55F64 FOREIGN KEY (map_id) REFERENCES ls_maps (id)');
        $this->addSql('ALTER TABLE ls_current_match ADD CONSTRAINT FK_C5572E27BC01C675 FOREIGN KEY (summoner_id) REFERENCES ls_summoner (id)');
        $this->addSql('ALTER TABLE ls_current_match ADD CONSTRAINT FK_C5572E27477B5BAE FOREIGN KEY (queue_id) REFERENCES ls_queues (id)');
        $this->addSql('ALTER TABLE ls_current_match ADD CONSTRAINT FK_C5572E27B0F7D5CB FOREIGN KEY (p1_spell1_id) REFERENCES ls_spells (id)');
        $this->addSql('ALTER TABLE ls_current_match ADD CONSTRAINT FK_C5572E27A2427A25 FOREIGN KEY (p1_spell2_id) REFERENCES ls_spells (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F63358362DF6AE32 FOREIGN KEY (streamer) REFERENCES ls_streamer (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F6335836F62F176 FOREIGN KEY (region) REFERENCES ls_regions (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F633583645437EB4 FOREIGN KEY (champion) REFERENCES ls_champions_legacy (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F6335836FF8FE01F FOREIGN KEY (enemy_champion_id) REFERENCES ls_champions_legacy (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F6335836ABE89763 FOREIGN KEY (summoner) REFERENCES ls_summoner (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F633583693ADAABB FOREIGN KEY (map) REFERENCES ls_maps (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F6335836477B5BAE FOREIGN KEY (queue_id) REFERENCES ls_queues (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F6335836B0F7D5CB FOREIGN KEY (p1_spell1_id) REFERENCES ls_spells (id)');
        $this->addSql('ALTER TABLE ls_matches ADD CONSTRAINT FK_F6335836A2427A25 FOREIGN KEY (p1_spell2_id) REFERENCES ls_spells (id)');
        $this->addSql('ALTER TABLE ls_reports ADD CONSTRAINT FK_10AEAC925F432AD FOREIGN KEY (streamer_id) REFERENCES ls_streamer (id)');
        $this->addSql('ALTER TABLE ls_reports ADD CONSTRAINT FK_10AEAC998260155 FOREIGN KEY (region_id) REFERENCES ls_regions (id)');
        $this->addSql('ALTER TABLE ls_smurf ADD CONSTRAINT FK_5EE2DA6A98260155 FOREIGN KEY (region_id) REFERENCES ls_regions (id)');
        $this->addSql('ALTER TABLE ls_smurf ADD CONSTRAINT FK_5EE2DA6A25F432AD FOREIGN KEY (streamer_id) REFERENCES ls_streamer (id)');
        $this->addSql('ALTER TABLE ls_streamer ADD CONSTRAINT FK_C90874743952D0CB FOREIGN KEY (platform) REFERENCES ls_platforms (id)');
        $this->addSql('ALTER TABLE ls_streamer_report ADD CONSTRAINT FK_8EBDC1A525F432AD FOREIGN KEY (streamer_id) REFERENCES ls_streamer (id)');
        $this->addSql('ALTER TABLE ls_summoner ADD CONSTRAINT FK_4F164D2598260155 FOREIGN KEY (region_id) REFERENCES ls_regions (id)');
        $this->addSql('ALTER TABLE ls_summoner ADD CONSTRAINT FK_4F164D2525F432AD FOREIGN KEY (streamer_id) REFERENCES ls_streamer (id)');
        $this->addSql('ALTER TABLE spell_summoner ADD CONSTRAINT FK_BF84AE69BC01C675 FOREIGN KEY (summoner_id) REFERENCES ls_summoner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spell_summoner ADD CONSTRAINT FK_BF84AE69479EC90D FOREIGN KEY (spell_id) REFERENCES ls_spells (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ls_summoner_report ADD CONSTRAINT FK_3F88B5C725F432AD FOREIGN KEY (streamer_id) REFERENCES ls_streamer (id)');
        $this->addSql('ALTER TABLE ls_summoner_report ADD CONSTRAINT FK_3F88B5C7BC01C675 FOREIGN KEY (summoner_id) REFERENCES ls_summoner (id)');
        $this->addSql('ALTER TABLE ls_vods ADD CONSTRAINT FK_23CD23E225F432AD FOREIGN KEY (streamer_id) REFERENCES ls_streamer (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ls_current_match DROP FOREIGN KEY FK_C5572E27FA7FD7EB');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F633583645437EB4');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F6335836FF8FE01F');
        $this->addSql('ALTER TABLE ls_current_match DROP FOREIGN KEY FK_C5572E2753C55F64');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F633583693ADAABB');
        $this->addSql('ALTER TABLE ls_streamer DROP FOREIGN KEY FK_C90874743952D0CB');
        $this->addSql('ALTER TABLE ls_current_match DROP FOREIGN KEY FK_C5572E27477B5BAE');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F6335836477B5BAE');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F6335836F62F176');
        $this->addSql('ALTER TABLE ls_reports DROP FOREIGN KEY FK_10AEAC998260155');
        $this->addSql('ALTER TABLE ls_smurf DROP FOREIGN KEY FK_5EE2DA6A98260155');
        $this->addSql('ALTER TABLE ls_summoner DROP FOREIGN KEY FK_4F164D2598260155');
        $this->addSql('ALTER TABLE ls_current_match DROP FOREIGN KEY FK_C5572E27B0F7D5CB');
        $this->addSql('ALTER TABLE ls_current_match DROP FOREIGN KEY FK_C5572E27A2427A25');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F6335836B0F7D5CB');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F6335836A2427A25');
        $this->addSql('ALTER TABLE spell_summoner DROP FOREIGN KEY FK_BF84AE69479EC90D');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F63358362DF6AE32');
        $this->addSql('ALTER TABLE ls_reports DROP FOREIGN KEY FK_10AEAC925F432AD');
        $this->addSql('ALTER TABLE ls_smurf DROP FOREIGN KEY FK_5EE2DA6A25F432AD');
        $this->addSql('ALTER TABLE ls_streamer_report DROP FOREIGN KEY FK_8EBDC1A525F432AD');
        $this->addSql('ALTER TABLE ls_summoner DROP FOREIGN KEY FK_4F164D2525F432AD');
        $this->addSql('ALTER TABLE ls_summoner_report DROP FOREIGN KEY FK_3F88B5C725F432AD');
        $this->addSql('ALTER TABLE ls_vods DROP FOREIGN KEY FK_23CD23E225F432AD');
        $this->addSql('ALTER TABLE ls_current_match DROP FOREIGN KEY FK_C5572E27BC01C675');
        $this->addSql('ALTER TABLE ls_matches DROP FOREIGN KEY FK_F6335836ABE89763');
        $this->addSql('ALTER TABLE spell_summoner DROP FOREIGN KEY FK_BF84AE69BC01C675');
        $this->addSql('ALTER TABLE ls_summoner_report DROP FOREIGN KEY FK_3F88B5C7BC01C675');
        $this->addSql('DROP TABLE ls_champions_legacy');
        $this->addSql('DROP TABLE ls_current_match');
        $this->addSql('DROP TABLE ls_live');
        $this->addSql('DROP TABLE ls_maps');
        $this->addSql('DROP TABLE ls_matches');
        $this->addSql('DROP TABLE ls_perks');
        $this->addSql('DROP TABLE ls_platforms');
        $this->addSql('DROP TABLE ls_queues');
        $this->addSql('DROP TABLE ls_regions');
        $this->addSql('DROP TABLE ls_reports');
        $this->addSql('DROP TABLE ls_smurf');
        $this->addSql('DROP TABLE ls_spells');
        $this->addSql('DROP TABLE ls_streamer');
        $this->addSql('DROP TABLE ls_streamer_report');
        $this->addSql('DROP TABLE ls_summoner');
        $this->addSql('DROP TABLE spell_summoner');
        $this->addSql('DROP TABLE ls_summoner_report');
        $this->addSql('DROP TABLE ls_versions_all');
        $this->addSql('DROP TABLE ls_versions');
        $this->addSql('DROP TABLE ls_vods');
    }
}
