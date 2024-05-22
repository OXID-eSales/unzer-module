<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240321104606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('oscunzertmporder')) {
            $this->createTmpOrderTable();
        }
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
    protected function createTmpOrderTable()
    {
        $this->addSql("CREATE TABLE `oscunzertmporder` (
                              `OXID` char(32) NOT NULL,
                              `OXSHOPID` int(11) NOT NULL,
                              `OXORDERID` char(32) NOT NULL,
                              `OXUNZERORDERNR` int(11) NOT NULL,
                              `TMPORDER` mediumtext NOT NULL,
                              `STATUS` enum('FINISHED','NOT_FINISHED') NOT NULL,
                              `TIMESTAMP` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
    }
}
