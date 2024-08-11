<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811102027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'persists if the user selected the #oscunzersavepayment checkbox to save the credit card payment or the paypal account for easier further payments';
    }

    public function up(Schema $schema): void
    {
        $this->updateTransactionTable($schema);
    }
    protected function updateTransactionTable(Schema $schema): void
    {
        $transaction = $schema->getTable('oscunzertransaction');

        if (!$transaction->hasColumn('SAVEPAYMENT')) {
            $this->addSql("ALTER TABLE oscunzertransaction ADD SAVEPAYMENT BOOLEAN NOT NULL DEFAULT 0 COMMENT '{$this->getDescription()}';");
        }
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
