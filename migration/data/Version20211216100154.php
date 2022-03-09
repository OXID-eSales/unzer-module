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
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Facts\Facts;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211216100154 extends AbstractMigration
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
        $this->createTransactionTable($schema);
        $this->changeUserTable($schema);
    }

    public function down(Schema $schema): void
    {
        //dropping column 'customerid' in oxuser table
        $oxuser = $schema->getTable('oxuser');
        $oxuser->dropColumn('CUSTOMERID');

        // dropping table 'oscunzertransaction'
        $schema->dropTable('oscunzertransaction');

        //deleting all unzer related rdfapayment entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'rdfapayment'");

        //deleting all unzer related oxdelset entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'oxdelset'");

        //deleting all unzer related entries from oxpayments table
        $this->addSql("DELETE FROM `oxpayments` where `OXID` like 'oscunzer_%'");

        //deleting all unzer related entries from oxcontents table
        $this->addSql("DELETE FROM `oxcontents` where `OXLOADID` like 'oscunzer%'");

        //deleting all unzer related oxcountry entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'oxcountry'");
    }

    /**
     * create Transaction Table
     */
    protected function createTransactionTable(Schema $schema): void
    {
        if (!$schema->hasTable('oscunzertransaction')) {
            $transaction = $schema->createTable('oscunzertransaction');
        } else {
            $transaction = $schema->getTable('oscunzertransaction');
        }

        if (!$transaction->hasColumn('OXID')) {
            $transaction->addColumn('OXID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci']);
        }
        if (!$transaction->hasColumn('SHORTID')) {
            $transaction->addColumn('SHORTID', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('TRACEID')) {
            $transaction->addColumn('TRACEID', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('OXORDERID')) {
            $transaction->addColumn('OXORDERID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci', 'comment' => 'OXID (oxorder)']);
        }
        if (!$transaction->hasColumn('OXSHOPID')) {
            $transaction->addColumn('OXSHOPID', Types::INTEGER, ['columnDefinition' => 'int(11)', 'default' => 1, 'comment' => 'Shop ID (oxshops)']);
        }
        if (!$transaction->hasColumn('OXUSERID')) {
            $transaction->addColumn('OXUSERID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci', 'comment' => 'OXID (oxuser)']);
        }
        if (!$transaction->hasColumn('AMOUNT')) {
            $transaction->addColumn('AMOUNT', Types::FLOAT, ['columnDefinition' => 'DOUBLE', 'default' => 0]);
        }
        if (!$transaction->hasColumn('CURRENCY')) {
            $transaction->addColumn('CURRENCY', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('TYPEID')) {
            $transaction->addColumn('TYPEID', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('METADATA')) {
            $transaction->addColumn('METADATA', Types::JSON, ['default' => ""]);
        }
        if (!$transaction->hasColumn('CUSTOMERID')) {
            $transaction->addColumn('CUSTOMERID', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('OXACTIONDATE')) {
            $transaction->addColumn('OXACTIONDATE', Types::DATETIME_MUTABLE, ['columnDefinition' => 'timestamp default "0000-00-00 00:00:00"']);
        }
        if (!$transaction->hasColumn('OXACTION')) {
            $transaction->addColumn('OXACTION', Types::STRING, ['default' => ""]);
        }
        if (!$transaction->hasColumn('SERIALIZED_BASKET')) {
            $transaction->addColumn('SERIALIZED_BASKET', Types::TEXT, ['default' => ""]);
        }
        if (!$transaction->hasColumn('OXTIMESTAMP')) {
            $transaction->addColumn('OXTIMESTAMP', Types::DATETIME_MUTABLE, ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']);
        }
        if (!$transaction->hasPrimaryKey()) {
            $transaction->setPrimaryKey(['OXID']);
        }
        if (!$transaction->hasIndex('TRANSACTIONUNIQUE')) {
            $transaction->addUniqueIndex(['OXSHOPID', 'OXORDERID', 'OXUSERID', 'AMOUNT', 'SHORTID', 'CUSTOMERID', 'OXACTION'], 'TRANSACTIONUNIQUE');
        }
    }

    /**
     * change User Table
     */
    protected function changeUserTable(Schema $schema): void
    {
        //adding new column 'customerid' in oxuser table
        $oxuser = $schema->getTable('oxuser');
        if (!$oxuser->hasColumn('CUSTOMERID')) {
            $oxuser->addColumn('CUSTOMERID', Types::STRING, ['default' => "", 'comment' => 'unzer customerid']);
        }
    }
}
