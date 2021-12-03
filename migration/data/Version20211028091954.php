<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211028091954 extends AbstractMigration
{
    /**
     * @throws Exception
     */
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
        //create transaction table
        $transaction = $schema->createTable('oscunzertransaction');
        $transaction->addColumn('OXID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci']);
        $transaction->addColumn('OXORDERID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci', 'comment' => 'OXID (oxorder)']);
        $transaction->addColumn('OXSHOPID', Types::INTEGER, ['columnDefinition' => 'int(11)', 'default' => 1, 'comment' => 'Shop ID (oxshops)']);
        $transaction->addColumn('OXUSERID', Types::STRING, ['columnDefinition' => 'char(32) collate latin1_general_ci', 'comment' => 'OXID (oxuser)']);
        $transaction->addColumn('AMOUNT', Types::FLOAT, ['columnDefinition' => 'DOUBLE', 'default' => 0]);
        $transaction->addColumn('CURRENCY', Types::STRING, ['default' => ""]);
        $transaction->addColumn('TYPEID', Types::STRING, ['default' => ""]);
        $transaction->addColumn('METADATAID', Types::STRING, ['default' => ""]);
        $transaction->addColumn('METADATA', Types::JSON, ['default' => ""]);
        $transaction->addColumn('CUSTOMERID', Types::STRING, ['default' => ""]);
        $transaction->addColumn('OXACTIONDATE', Types::DATETIME_MUTABLE, ['columnDefinition' => 'timestamp default "0000-00-00 00:00:00"']);
        $transaction->addColumn('OXACTION', Types::STRING, ['default' => ""]);
        $transaction->addColumn('OXTIMESTAMP', Types::DATETIME_MUTABLE, ['columnDefinition' => 'timestamp default current_timestamp on update current_timestamp']);
        $transaction->setPrimaryKey(['OXID']);
        $transaction->addindex(['OXSHOPID', 'OXORDERID']);

        //adding new oxpayment column
        $oxpayment = $schema->getTable('oxpayments');
        $oxpayment->addColumn('OXPAYMENTPROCEDURE', Types::STRING, ['default' => 'direct Capture']);

        //adding new oxpayment column
        $oxuser = $schema->getTable('oxuser');
        $oxuser->addColumn('CUSTOMERID', Types::STRING, ['default' => "", 'comment' => 'unzer customerid']);
    }

    public function down(Schema $schema): void
    {
        //tbd
    }
}
