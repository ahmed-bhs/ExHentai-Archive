<?php declare(strict_types=1);

namespace DoctrineMigrations;

use App\Model\GalleryToken;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Process;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180907193346 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Migrate Tag namespaces
        $this->addSql('INSERT INTO exhentai_tag_namespace (name) SELECT name FROM tagnamespace');
        // Migrate Tags
        $this->addSql('INSERT INTO exhentai_tag (namespace_id, name) SELECT etn.id as newnamespaceid, t.`name` as `name` FROM gallery_tag gt INNER JOIN tag t on gt.tag_id = t.id INNER JOIN tagnamespace tn on gt.namespace_id = tn.id INNER JOIN exhentai_tag_namespace etn ON etn.`name` = tn.`name` GROUP BY name');
        // Insert available categories (see wiki->API)
        $this->addSql('INSERT INTO exhentai_category (title, oldname) VALUES ("Doujinshi","doujinshi"),("Manga","manga"),("Artist CG Sets","artistcg"),("Game CG Sets","gamecg"),("Western","western"),("Image Sets","imageset"),("Non-H","non-h"),("Cosplay","cosplay"),("Asian Porn","asianporn"),("Misc","misc"),("Private","private")');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
