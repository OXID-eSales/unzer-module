<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240603142027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->updateTransactionTable($schema);
    }
    protected function updateTransactionTable(Schema $schema): void
    {
        $transaction = $schema->getTable('oscunzertransaction');

        if (!$transaction->hasColumn('CANCELREASON')) {
            $transaction->addColumn('CANCELREASON', Types::STRING, ['default' => '']);
        }
    }
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
