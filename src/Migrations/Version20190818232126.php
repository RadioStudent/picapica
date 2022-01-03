<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190818232126 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE data_label (ID INT AUTO_INCREMENT NOT NULL, NAME VARCHAR(255) NOT NULL, INDEX name (name), PRIMARY KEY(ID)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rel_album2label (album_id INT NOT NULL, label_id INT NOT NULL, INDEX IDX_C2398E01137ABCF (album_id), INDEX IDX_C2398E033B92F39 (label_id), PRIMARY KEY(album_id, label_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rel_album2label ADD CONSTRAINT FK_C2398E01137ABCF FOREIGN KEY (album_id) REFERENCES data_albums (ID)');
        $this->addSql('ALTER TABLE rel_album2label ADD CONSTRAINT FK_C2398E033B92F39 FOREIGN KEY (label_id) REFERENCES data_label (ID)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_album2label DROP FOREIGN KEY FK_C2398E033B92F39');
        $this->addSql('DROP TABLE data_label');
        $this->addSql('DROP TABLE rel_album2label');
    }
}
