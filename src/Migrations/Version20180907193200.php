<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create the new DB schema
 */
final class Version20180907193200 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE exhentai_archiver_key (id INT AUTO_INCREMENT NOT NULL, gallery_id INT NOT NULL, token VARCHAR(255) NOT NULL, time DATETIME NOT NULL, UNIQUE INDEX UNIQ_E7739C314E7AF8F (gallery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exhentai_category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, oldname VARCHAR(255) NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exhentai_gallery (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, archiver_key_id INT DEFAULT NULL, token VARCHAR(12) NOT NULL, title VARCHAR(1000) DEFAULT NULL, title_japan VARCHAR(1000) DEFAULT NULL, posted DATETIME NOT NULL, uploader VARCHAR(255) DEFAULT NULL, filesize DOUBLE PRECISION DEFAULT NULL, file_count INT NOT NULL, expunged TINYINT(1) DEFAULT NULL, rating DOUBLE PRECISION NOT NULL, torrent_count INT NOT NULL, download_state INT NOT NULL, INDEX IDX_DD12754512469DE2 (category_id), UNIQUE INDEX UNIQ_DD127545FB7424C8 (archiver_key_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exhentai_gallery_exhentai_tag (exhentai_gallery_id INT NOT NULL, exhentai_tag_id INT NOT NULL, INDEX IDX_A40EDFBFB1E1763D (exhentai_gallery_id), INDEX IDX_A40EDFBF3C6CAA90 (exhentai_tag_id), PRIMARY KEY(exhentai_gallery_id, exhentai_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exhentai_gallery_image (id INT AUTO_INCREMENT NOT NULL, gallery_id INT NOT NULL, type INT NOT NULL, page INT NOT NULL, INDEX IDX_5EE325D44E7AF8F (gallery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exhentai_tag (id INT AUTO_INCREMENT NOT NULL, namespace_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_BE6B10B95F74F783 (namespace_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exhentai_tag_namespace (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exhentai_archiver_key ADD CONSTRAINT FK_E7739C314E7AF8F FOREIGN KEY (gallery_id) REFERENCES exhentai_gallery (id)');
        $this->addSql('ALTER TABLE exhentai_gallery ADD CONSTRAINT FK_DD12754512469DE2 FOREIGN KEY (category_id) REFERENCES exhentai_category (id)');
        $this->addSql('ALTER TABLE exhentai_gallery ADD CONSTRAINT FK_DD127545FB7424C8 FOREIGN KEY (archiver_key_id) REFERENCES exhentai_archiver_key (id)');
        $this->addSql('ALTER TABLE exhentai_gallery_exhentai_tag ADD CONSTRAINT FK_A40EDFBFB1E1763D FOREIGN KEY (exhentai_gallery_id) REFERENCES exhentai_gallery (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exhentai_gallery_exhentai_tag ADD CONSTRAINT FK_A40EDFBF3C6CAA90 FOREIGN KEY (exhentai_tag_id) REFERENCES exhentai_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE exhentai_gallery_image ADD CONSTRAINT FK_5EE325D44E7AF8F FOREIGN KEY (gallery_id) REFERENCES exhentai_gallery (id)');
        $this->addSql('ALTER TABLE exhentai_tag ADD CONSTRAINT FK_BE6B10B95F74F783 FOREIGN KEY (namespace_id) REFERENCES exhentai_tag_namespace (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exhentai_gallery DROP FOREIGN KEY FK_DD127545FB7424C8');
        $this->addSql('ALTER TABLE exhentai_gallery DROP FOREIGN KEY FK_DD12754512469DE2');
        $this->addSql('ALTER TABLE exhentai_archiver_key DROP FOREIGN KEY FK_E7739C314E7AF8F');
        $this->addSql('ALTER TABLE exhentai_gallery_exhentai_tag DROP FOREIGN KEY FK_A40EDFBFB1E1763D');
        $this->addSql('ALTER TABLE exhentai_gallery_image DROP FOREIGN KEY FK_5EE325D44E7AF8F');
        $this->addSql('ALTER TABLE exhentai_gallery_exhentai_tag DROP FOREIGN KEY FK_A40EDFBF3C6CAA90');
        $this->addSql('ALTER TABLE exhentai_tag DROP FOREIGN KEY FK_BE6B10B95F74F783');$this->addSql('DROP TABLE exhentai_archiver_key');
        $this->addSql('DROP TABLE exhentai_category');
        $this->addSql('DROP TABLE exhentai_gallery');
        $this->addSql('DROP TABLE exhentai_gallery_exhentai_tag');
        $this->addSql('DROP TABLE exhentai_gallery_image');
        $this->addSql('DROP TABLE exhentai_tag');
        $this->addSql('DROP TABLE exhentai_tag_namespace');

    }
}
