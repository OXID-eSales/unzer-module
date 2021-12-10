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

        foreach ($this->getRDFinserts() as $oxid => $aRDF) {
            $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [$oxid, $aRDF['oxpaymentid'], $aRDF['oxobjectid'], 'rdfapayment']);
        }
    }

    public function down(Schema $schema): void
    {
        //deleting all unzer related rdfapayment entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'rdfapayment'");

        //deleting all unzer related oxdelset entries from oxobject2payment table
        $this->addSql("DELETE FROM `oxobject2payment` where `OXPAYMENTID` like 'oscunzer_%' and `OXTYPE` = 'oxdelset'");
    }

    protected function getRDFinserts(): array
    {
        return [
            'oscunzer_card_mastercard' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'MasterCard',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_visa' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'VISA',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_americanexpress' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'AmericanExpress',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_dinersclub' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'DinersClub',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_card_jcb' => [
                'oxpaymentid' => 'oscunzer_card',
                'oxobjectid' => 'JCB',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_prepayment' => [
                'oxpaymentid' => 'oscunzer_prepayment',
                'oxobjectid' => 'ByBankTransferInAdvance',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_pis' => [
                'oxpaymentid' => 'oscunzer_pis',
                'oxobjectid' => 'ByBankTransferInAdvance',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_invoice' => [
                'oxpaymentid' => 'oscunzer_invoice',
                'oxobjectid' => 'ByInvoice',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_invoice-secured' => [
                'oxpaymentid' => 'oscunzer_invoice-secured',
                'oxobjectid' => 'ByInvoice',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_sepa' => [
                'oxpaymentid' => 'oscunzer_sepa',
                'oxobjectid' => 'DirectDebit',
                'oxtype' => 'rdfapayment',
            ],
            'ooscunzer_sepa-secured' => [
                'oxpaymentid' => 'oscunzer_sepa-secured',
                'oxobjectid' => 'DirectDebit',
                'oxtype' => 'rdfapayment',
            ],
            'oscunzer_paypal' => [
                'oxpaymentid' => 'oscunzer_paypal',
                'oxobjectid' => 'PayPal',
                'oxtype' => 'rdfapayment',
            ],
        ];
    }
}
