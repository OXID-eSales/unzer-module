<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use OxidSolutionCatalysts\Unzer\Model\TmpFetchPayment;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240506121420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable(TmpFetchPayment::CORE_TABLE)) {
            $this->createTmpOrderTable();
        }
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
    protected function createTmpOrderTable()
    {
        $this->addSql("CREATE TABLE `" . TmpFetchPayment::CORE_TABLE . "` (
                              `OXID` char(32) NOT NULL,
                              `OXSHOPID` int(11) NOT NULL,
                              `FETCHPAYMENT` longtext NOT NULL,
                              `TIMESTAMP` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
    }
}
