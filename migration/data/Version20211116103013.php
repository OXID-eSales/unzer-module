<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211116103013 extends AbstractMigration
{
    protected $_aCountry = [
        "Belgien" => "a7c40f632e04633c9.47194042",
        "Deutschland" => "a7c40f631fc920687.20179984",
        "Estland" => "8f241f110958b69e4.93886171",
        "Finnland" => "a7c40f63293c19d65.37472814",
        "Frankreich" => "a7c40f63272a57296.32117580",
        "Griechenland" => "a7c40f633114e8fc6.25257477",
        "Irland" => "a7c40f632be4237c2.48517912",
        "Italien" => "a7c40f6323c4bfb36.59919433",
        "Lettland" => "8f241f11095cf2ea6.73925511",
        "Litauen" => "8f241f11095d6ffa8.86593236",
        "Luxemburg" => "a7c40f63264309e05.58576680",
        "Malta" => "8f241f11095e36eb3.69050509",
        "Niederlande" => "a7c40f632cdd63c52.64272623",
        "Portugal" => "a7c40f632f65bd8e2.84963272",
        "Slowakei" => "8f241f1109647a265.29938154",
        "Slowenien" => "8f241f11096497149.85116254",
        "Spanien" => "a7c40f633038cd578.22975442",
        "Zypern" => "8f241f110957b6896.52725150",
        "Österreich" => "a7c40f6320aeb2ec2.72885259",
        "Schweden" => "a7c40f632848c5217.53322339",
        "Norwegen" => "8f241f11096176795.61257067",
        "Dänemark" => "8f241f110957e6ef8.56458418",
        "Schweiz" => "a7c40f6321c6f6109.43859248",
        "Polen" => "8f241f1109624d3f8.50953605",
        "Großbritannien" => "a7c40f632a0804ab5.18804076",
        "Tschechien" => "8f241f110957cb457.97820918",
        "Australien" => "8f241f11095410f38.37165361",
        "USA" => "8f241f11096877ac0.98748826",
        "Ungarn" => "8f241f11095b3e016.98213173",
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        //Payment -> Country Assignment

        //bancontact
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_bancontact." . $this->_aCountry['Belgien'] . ".oxcountry"), 'oscunzer_bancontact', $this->_aCountry['Belgien'], 'oxcountry']);

        //invoice
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Belgien'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Belgien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Deutschland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Estland'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Estland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Finnland'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Finnland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Frankreich'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Frankreich'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Griechenland'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Griechenland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Irland'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Irland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Italien'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Italien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Lettland'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Lettland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Litauen'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Litauen'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Luxemburg'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Luxemburg'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Malta'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Malta'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Niederlande'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Niederlande'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Portugal'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Portugal'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Slowakei'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Slowakei'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Slowenien'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Slowenien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Spanien'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Spanien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Zypern'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Zypern'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_invoice', $this->_aCountry['Österreich'], 'oxcountry']);

        //invoice-secured
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice-secured." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_invoice-secured', $this->_aCountry['Deutschland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_invoice-secured." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_invoice-secured', $this->_aCountry['Österreich'], 'oxcountry']);

        //pis
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_pis." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_pis', $this->_aCountry['Deutschland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_pis." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_pis', $this->_aCountry['Österreich'], 'oxcountry']);

        //installment
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_installment." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_installment', $this->_aCountry['Deutschland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_installment." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_installment', $this->_aCountry['Österreich'], 'oxcountry']);

        //prepayment
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Belgien'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Belgien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Deutschland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Estland'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Estland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Finnland'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Finnland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Frankreich'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Frankreich'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Griechenland'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Griechenland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Irland'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Irland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Italien'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Italien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Lettland'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Lettland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Litauen'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Litauen'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Luxemburg'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Luxemburg'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Malta'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Malta'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Niederlande'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Niederlande'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Portugal'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Portugal'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Slowakei'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Slowakei'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Slowenien'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Slowenien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Spanien'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Spanien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Zypern'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Zypern'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_prepayment." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_prepayment', $this->_aCountry['Österreich'], 'oxcountry']);

        //sepa-direct-debit-secured
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa-secured." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_sepa-secured', $this->_aCountry['Deutschland'], 'oxcountry']);

        //giropay
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_giropay." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_giropay', $this->_aCountry['Deutschland'], 'oxcountry']);

        //ideal
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_ideal." . $this->_aCountry['Niederlande'] . ".oxcountry"), 'oscunzer_ideal', $this->_aCountry['Niederlande'], 'oxcountry']);

        //eps
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_eps." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_eps', $this->_aCountry['Österreich'], 'oxcountry']);

        //przelewy24
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_przelewy24." . $this->_aCountry['Polen'] . ".oxcountry"), 'oscunzer_przelewy24', $this->_aCountry['Polen'], 'oxcountry']);

        //sepa-direct-debit
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Belgien'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Belgien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Deutschland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Estland'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Estland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Finnland'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Finnland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Frankreich'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Frankreich'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Griechenland'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Griechenland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Irland'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Irland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Italien'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Italien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Lettland'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Lettland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Litauen'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Litauen'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Luxemburg'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Luxemburg'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Malta'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Malta'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Niederlande'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Niederlande'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Portugal'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Portugal'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Slowakei'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Slowakei'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Slowenien'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Slowenien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Spanien'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Spanien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Zypern'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Zypern'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sepa." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_sepa', $this->_aCountry['Österreich'], 'oxcountry']);

        //sofort
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Schweden'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Schweden'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Norwegen'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Norwegen'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Finnland'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Finnland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Dänemark'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Dänemark'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Deutschland'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Deutschland'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Niederlande'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Niederlande'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Belgien'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Belgien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Schweiz'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Schweiz'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Frankreich'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Frankreich'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Italien'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Italien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Polen'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Polen'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Spanien'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Spanien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Portugal'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Portugal'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Großbritannien'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Großbritannien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Ungarn'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Ungarn'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Tschechien'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Tschechien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Slowakei'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Slowakei'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Australien'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Australien'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['USA'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['USA'], 'oxcountry']);
        $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5("oscunzer_sofort." . $this->_aCountry['Österreich'] . ".oxcountry"), 'oscunzer_sofort', $this->_aCountry['Österreich'], 'oxcountry']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
