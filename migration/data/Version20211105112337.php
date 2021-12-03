<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use OxidSolutionCatalysts\Unzer\Core\UnzerHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211105112337 extends AbstractMigration
{
    private static $aPayments = ['oscunzer_alipay', 'oscunzer_bancontact', 'oscunzer_card',
        'oscunzer_cardrecurring', 'oscunzer_eps', 'oscunzer_giropay', 'oscunzer_ideal',
        'oscunzer_installment', 'oscunzer_invoice', 'oscunzer_invoice-secured', 'oscunzer_paypal',
        'oscunzer_pis', 'oscunzer_prepayment', 'oscunzer_przelewy24', 'oscunzer_sepa',
        'oscunzer_sepa-secured', 'oscunzer_sofort', 'oscunzer_unzerpayment', 'oscunzer_wechatpay'];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `oxpayments`
          SET `OXFROMAMOUNT` = 10,`OXTOAMOUNT` = 1000
         WHERE (`OXID` = 'oscunzer_sepa-secured' || `OXID` = 'oscunzer_invoice-secured')
          ;");

        foreach (self::$aPayments as $paymentid) {
            $oxid = md5($paymentid . "oxidstandard.oxdelset");
            $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            select '" . $oxid . "', '" . $paymentid . "', 'oxidstandard', 'oxdelset'
                            from oxdeliveryset where oxid = 'oxidstandard' ");
        }

        foreach (UnzerHelper::getRDFinserts() as $oxid => $aRDF) {
            $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [$oxid, $aRDF['oxpaymentid'], $aRDF['oxobjectid'], 'rdfapayment']);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
