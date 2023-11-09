<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107212500 extends AbstractMigration
{
    /** @throws Exception */
    public function __construct($version)
    {
        parent::__construct($version);

        $this->platform->registerDoctrineTypeMapping('enum', 'string');
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->updateOxOrderTable($schema);
    }

    public function down(Schema $schema): void
    {
    }

    protected function updateOxOrderTable(Schema $schema): void
    {
        $oxorder = $schema->getTable('oxorder');
        if (!$oxorder->hasColumn('OXUNZERORDERNR')) {
            $oxorder->addColumn('OXUNZERORDERNR', Types::INTEGER, ['columnDefinition' => 'int(11)', 'default' => 0, 'comment' => 'Unzer Order Nr']);
        }
    }
}
