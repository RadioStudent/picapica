<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190818154902 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_album2herkunft DROP FOREIGN KEY FK_8A2869F61137ABCF');
        $this->addSql('ALTER TABLE rel_album2herkunft DROP FOREIGN KEY FK_8A2869F620B66B9');
        $this->addSql('ALTER TABLE rel_album2herkunft ADD CONSTRAINT FK_8A2869F61137ABCF FOREIGN KEY (album_id) REFERENCES data_albums (ID)');
        $this->addSql('ALTER TABLE rel_album2herkunft ADD CONSTRAINT FK_8A2869F620B66B9 FOREIGN KEY (herkunft_id) REFERENCES data_herkunft (ID)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_album2herkunft DROP FOREIGN KEY FK_8A2869F61137ABCF');
        $this->addSql('ALTER TABLE rel_album2herkunft DROP FOREIGN KEY FK_8A2869F620B66B9');
        $this->addSql('ALTER TABLE rel_album2herkunft ADD CONSTRAINT FK_8A2869F61137ABCF FOREIGN KEY (album_id) REFERENCES data_albums (ID) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rel_album2herkunft ADD CONSTRAINT FK_8A2869F620B66B9 FOREIGN KEY (herkunft_id) REFERENCES data_herkunft (ID) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
