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
    }

    public function down(Schema $schema): void
    {
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
                if (isset($paymentDefinitions[$langAbbr . '_desc'])) {
                    $langRows .= ($langId == 0) ? ', `OXDESC`, `OXLONGDESC`' :
                        sprintf(', `OXDESC_%s`, `OXLONGDESC_%s`', $langId, $langId);
                    $sqlPlaceHolder .= ', ?, ?';
                    $sqlValues[] = $paymentDefinitions[$langAbbr . '_desc'];
                    $sqlValues[] = $paymentDefinitions[$langAbbr . '_longdesc'];
                }
            }
            foreach ($paymentDefinitions['countries'] as $country) {
                if (array_key_exists($country, $this->getIsoCountries())) {
                    $this->addSql("INSERT IGNORE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`)
                            VALUES(?, ?, ?, ?)", [md5($paymentId . $this->getIsoCountries()[$country] . ".oxcountry"), $paymentId, $this->getIsoCountries()[$country], 'oxcountry']);
                }
            }

            $this->addSql(
                "INSERT IGNORE INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXADDSUMTYPE`
                " . $langRows . ")
                VALUES (" . $sqlPlaceHolder . ")",
                $sqlValues
            );
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
