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
final class Version20230328135000 extends AbstractMigration
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
        $this->updateTransactionTable($schema);
        $this->updateOxUserTable($schema);
    }

    public function down(Schema $schema): void
    {
    }

    protected function updateTransactionTable(Schema $schema): void
    {
        $transaction = $schema->getTable('oscunzertransaction');

        if (!$transaction->hasColumn('CUSTOMERTYPE')) {
            $transaction->addColumn('CUSTOMERTYPE', Types::STRING, ['columnDefinition' => 'char(10) collate latin1_general_ci', 'comment' => 'Unzer customer type (B2B, B2C) or empty']);
        }
    }

    protected function updateOxUserTable(Schema $schema): void
    {
        $oxuser = $schema->getTable('oxuser');
        $oxuser->dropColumn('CUSTOMERID');
    }
}
