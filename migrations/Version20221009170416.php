<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221009170416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX FK_81398E09F603EE73 ON customer');
        $this->addSql('DROP INDEX IDX_81398E09F603EE73 ON customer');
        $this->addSql('CREATE INDEX IDX_81398E09F603EE73 ON customer (vendor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_81398E09F603EE73 ON customer');
        $this->addSql('CREATE INDEX FK_81398E09F603EE73 ON customer (vendor_id)');
        $this->addSql('CREATE INDEX IDX_81398E09F603EE73 ON customer (id)');
    }
}