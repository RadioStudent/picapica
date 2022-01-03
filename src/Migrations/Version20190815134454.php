<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815134454 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rel_album2herkunft (album_id INT NOT NULL, herkunft_id INT NOT NULL, INDEX IDX_8A2869F61137ABCF (album_id), INDEX IDX_8A2869F620B66B9 (herkunft_id), PRIMARY KEY(album_id, herkunft_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data_herkunft (ID INT AUTO_INCREMENT NOT NULL, NAME VARCHAR(255) NOT NULL, INDEX name (name), PRIMARY KEY(ID)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rel_album2herkunft ADD CONSTRAINT FK_8A2869F61137ABCF FOREIGN KEY (album_id) REFERENCES data_albums (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rel_album2herkunft ADD CONSTRAINT FK_8A2869F620B66B9 FOREIGN KEY (herkunft_id) REFERENCES data_herkunft (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_album2herkunft DROP FOREIGN KEY FK_8A2869F620B66B9');
        $this->addSql('DROP TABLE rel_album2herkunft');
        $this->addSql('DROP TABLE data_herkunft');
    }
}
