<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190819000420 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_album2genre DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE rel_album2genre ADD album_id INT NOT NULL');
        $this->addSql('ALTER TABLE rel_album2genre ADD CONSTRAINT FK_81D4FBF01137ABCF FOREIGN KEY (album_id) REFERENCES data_albums (ID)');
        $this->addSql('ALTER TABLE rel_album2genre ADD CONSTRAINT FK_81D4FBF04296D31F FOREIGN KEY (genre_id) REFERENCES data_genre (ID)');
        $this->addSql('CREATE INDEX IDX_81D4FBF01137ABCF ON rel_album2genre (album_id)');
        $this->addSql('CREATE INDEX IDX_81D4FBF04296D31F ON rel_album2genre (genre_id)');
        $this->addSql('ALTER TABLE rel_album2genre ADD PRIMARY KEY (album_id, genre_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_album2genre DROP FOREIGN KEY FK_81D4FBF01137ABCF');
        $this->addSql('ALTER TABLE rel_album2genre DROP FOREIGN KEY FK_81D4FBF04296D31F');
        $this->addSql('DROP INDEX IDX_81D4FBF01137ABCF ON rel_album2genre');
        $this->addSql('DROP INDEX IDX_81D4FBF04296D31F ON rel_album2genre');
        $this->addSql('ALTER TABLE rel_album2genre DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE rel_album2genre DROP album_id');
        $this->addSql('ALTER TABLE rel_album2genre ADD PRIMARY KEY (genre_id)');
    }
}
