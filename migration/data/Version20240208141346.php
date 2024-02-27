<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208141346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addUniqueIndex($schema);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    protected function addUniqueIndex(Schema $schema)
    {
        $transaction = $schema->getTable('oscunzertransaction');
        if ($transaction->hasColumn('PAYMENTTYPEID')) {
            if ($transaction->hasIndex('TRANSACTIONUNIQUE')) {
                $index = $transaction->getIndex('TRANSACTIONUNIQUE');
                if ($index->getName() === 'TRANSACTIONUNIQUE') {
                    $columns = $index->getColumns();
                    if (!in_array('PAYMENTTYPEID', $columns)) {
                        // need to use sql and not the orm
                        $this->addSql('DROP INDEX TRANSACTIONUNIQUE ON oscunzertransaction');
                        $this->addSql('CREATE UNIQUE INDEX TRANSACTIONUNIQUE ON oscunzertransaction (OXSHOPID, OXORDERID, OXUSERID, AMOUNT, SHORTID(100), CUSTOMERID, OXACTION, PAYMENTTYPEID)');
                    }
                }
            }
        }
    }
}
