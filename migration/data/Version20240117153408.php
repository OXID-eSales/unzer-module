<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240117153408 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $oxorder = $schema->getTable('oxorder');
        if ($oxorder->hasColumn('OXUNZERORDERNR')) {
            $this->addSql("ALTER TABLE oxorder MODIFY COLUMN OXUNZERORDERNR varchar(50)");
        }
    }

    public function down(Schema $schema): void
    {
    }
}
