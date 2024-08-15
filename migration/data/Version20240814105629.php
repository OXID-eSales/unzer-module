<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814105629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'persists the user id of the payment which the user wants to save, whether the email for paypal or card number for credit card.';
    }

    public function up(Schema $schema): void
    {
        $this->updateTransactionTable($schema);
    }
    protected function updateTransactionTable(Schema $schema): void
    {
        $transaction = $schema->getTable('oscunzertransaction');

        if (!$transaction->hasColumn('SAVEPAYMENTUSERID')) {
            $this->addSql("ALTER TABLE oscunzertransaction ADD SAVEPAYMENTUSERID VARCHAR(256) DEFAULT '' COMMENT '{$this->getDescription()}';");
        }
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
