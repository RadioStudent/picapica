<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150920211134 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_artist2artist DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE rel_artist2artist ADD ID INT AUTO_INCREMENT NOT NULL, CHANGE ARTIST_ID ARTIST_ID INT DEFAULT NULL, CHANGE RELATED_ARTIST_ID RELATED_ARTIST_ID INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rel_artist2artist ADD PRIMARY KEY (ID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rel_artist2artist DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE rel_artist2artist DROP ID, CHANGE ARTIST_ID ARTIST_ID INT NOT NULL, CHANGE RELATED_ARTIST_ID RELATED_ARTIST_ID INT NOT NULL');
        $this->addSql('ALTER TABLE rel_artist2artist ADD PRIMARY KEY (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)');
    }
}
