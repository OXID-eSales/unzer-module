<?php

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Facts\Facts;
use OxidSolutionCatalysts\Unzer\Core\Events;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211129120012 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->createPayments();
        $this->createStaticContent();
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    protected $aIsoCountries;

    private function getIsoCountries()
    {
        if (!$this->aIsoCountries) {
            $sSql = "SELECT OXID, OXISOALPHA2 FROM oxcountry";
            $this->aIsoCountries = [];

            foreach ($this->connection->fetchAllAssociative($sSql) as $row) {
                $this->aIsoCountries [$row['OXISOALPHA2']] = $row['OXID'];
            }
        }

        return $this->aIsoCountries;
    }

    /**
     * create payments
     */
    protected function createPayments(): void
    {
        foreach (Events::getUnzerPayments() as $paymentId => $paymentDefinitions) {
            if ($paymentDefinitions['insert'] === 0) {
                continue;
            }
            $langRows = '';
            $sqlPlaceHolder = '?, ?, ?, ?, ?';
            $sqlValues = [$paymentId, 1, 0, 10000, 'abs'];
            foreach ($this->getLanguageIds() as $langId => $langAbbr) {
                $langRows .= ($langId == 0) ? ', `OXDESC`, `OXLONGDESC`' :
                    sprintf(', `OXDESC_%s`, `OXLONGDESC_%s`', $langId, $langId);
                $sqlPlaceHolder .= ', ?, ?';
                $sqlValues[] = $paymentDefinitions[$langAbbr . '_desc'];
                $sqlValues[] = $paymentDefinitions[$langAbbr . '_longdesc'];
            }

            $this->setCountriesToPayment($paymentDefinitions, $paymentId);

            $this->addSql(
                "INSERT IGNORE INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXADDSUMTYPE`
                " . $langRows . ")
                VALUES (" . $sqlPlaceHolder . ")",
                $sqlValues
            );
        }
    }

    protected function createStaticContent()
    {
        foreach (Events::getStaticVCMS() as $oContent) {
            $langRows = '';
            $sqlPlaceHolder = '?, ?, ?';
            $sqlValues = [md5($oContent['OXLOADID'] . '1'), $oContent['OXLOADID'], 1];
            foreach ($this->getLanguageIds() as $langId => $langAbbr) {
                if (isset($oContent['oxcontent_' . $langAbbr])) {
                    $langRows .= ($langId == 0) ? ', `OXTITLE`, `OXCONTENT`, `OXACTIVE`' :
                        sprintf(', `OXTITLE_%s`, `OXCONTENT_%s`, `OXACTIVE_%s`', $langId, $langId, $langId);
                    $sqlPlaceHolder .= ', ?, ?, ?';
                    $sqlValues[] = $oContent['oxtitle_' . $langAbbr];
                    $sqlValues[] = $oContent['oxcontent_' . $langAbbr];
                    $sqlValues[] = '1';
                }
            }

            $this->addSql(
                "INSERT IGNORE INTO `oxcontents` (`OXID`, `OXLOADID`, `OXSHOPID`
                " . $langRows . ")
                VALUES (" . $sqlPlaceHolder . ")",
                $sqlValues
            );
        }

        $this->addSql("INSERT IGNORE INTO `oxcontents` (`OXID`, `OXLOADID`, `OXSHOPID`
                " . $langRows . ")
        SELECT md5(CONCAT(OXLOADID, s.OXID)), OXLOADID, s.OXID " .
        $this->getPrefixColumns($langRows, 'c') .
        " FROM oxcontents c, oxshops s
        WHERE OXLOADID IN ('oscunzersepamandatetext', 'oscunzersepamandateconfirmation') AND c.OXSHOPID=1");
    }

    protected function getPrefixColumns($langRows, $tablePrefix)
    {
        return str_replace(', ', ', ' . $tablePrefix . '.', $langRows);
    }

    protected function setCountriesToPayment($paymentDefinitions, $paymentId)
    {
        foreach ($paymentDefinitions['countries'] as $country) {
            if (array_key_exists($country, $this->getIsoCountries())) {
                $this->addSql(
                    "INSERT IGNORE INTO `oxobject2payment`
                    (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                    VALUES(?, ?, ?, ?)",
                    [
                        md5($paymentId . $this->getIsoCountries()[$country] . ".oxcountry"),
                        $paymentId,
                        $this->getIsoCountries()[$country],
                        'oxcountry'
                    ]
                );
            }
        }
    }

    /**
     * get the language-IDs
     */
    protected function getLanguageIds()
    {
        if (is_null($this->languageIds)) {
            $this->languageIds = [];

            $facts = new Facts();
            $configFile = new ConfigFile($facts->getSourcePath() . '/config.inc.php');
            $configKey = is_null($configFile->getVar('sConfigKey')) ?
                Config::DEFAULT_CONFIG_KEY :
                $configFile->getVar('sConfigKey');

            if (
                $results = $this->connection->executeQuery(
                    'SELECT DECODE(OXVARVALUE, ?) as confValue FROM `oxconfig` WHERE `OXVARNAME` = ?',
                    [$configKey, 'aLanguages']
                )->fetchAllAssociative()
            ) {
                $rawLanguageIds = unserialize($results[0]['confValue']);

                foreach ($rawLanguageIds as $langAbbr => $langName) {
                    $this->languageIds[] = $langAbbr;
                }
            }

            // fallback OXID-Standard
            if (!count($this->languageIds)) {
                $this->languageIds = ['de', 'en'];
            }
        }
        return $this->languageIds;
    }
}
