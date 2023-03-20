<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Driver\Result;
use PDO;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidEsales\Eshop\Application\Model\Content as EshopModelContent;
use OxidEsales\Eshop\Application\Model\Payment as EshopModelPayment;
use OxidEsales\Eshop\Core\Model\BaseModel as EshopBaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;

//NOTE: later we will do this on module installation, for now on first activation
class StaticContent
{
    /** @var QueryBuilderFactoryInterface */
    private $queryBuilderFactory;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function ensureUnzerPaymentMethods(): void
    {
        foreach (UnzerDefinitions::getUnzerDefinitions() as $paymentId => $paymentDefinitions) {
            $paymentMethod = oxNew(EshopModelPayment::class);
            if ($paymentMethod->load($paymentId)) {
                continue;
            }
            $this->createPaymentMethod($paymentId, $paymentDefinitions);
            $this->assignPaymentToCountries($paymentId, $paymentDefinitions['countries']);
            $this->assignPaymentToActiveDeliverySets($paymentId);
        }
    }

    protected function assignPaymentToActiveDeliverySets(string $paymentId): void
    {
        $deliverySetIds = $this->getActiveDeliverySetIds();
        foreach ($deliverySetIds as $deliverySetId) {
            $this->assignPaymentToDelivery($paymentId, $deliverySetId);
        }
    }

    protected function assignPaymentToCountries(string $paymentId, array $countries): void
    {
        $activeCountries = array_flip($this->getActiveCountries());
        $assignToCountries = [];
        foreach ($countries as $countryIsoAlpha2) {
            if (isset($activeCountries[strtoupper($countryIsoAlpha2)])) {
                $assignToCountries[] = $activeCountries[strtoupper($countryIsoAlpha2)];
            }
        }
        $assignToCountries = empty($assignToCountries) ? $activeCountries : $assignToCountries;

        foreach ($assignToCountries as $countryId) {
            $this->assignPaymentToCountry($paymentId, $countryId);
        }
    }

    /**
     * @param string $paymentId
     * @param string $countryId
     * @return void
     * @throws \Exception
     */
    protected function assignPaymentToCountry(string $paymentId, string $countryId): void
    {
        $object2Paymentent = oxNew(EshopBaseModel::class);
        $object2Paymentent->init('oxobject2payment');
        $object2Paymentent->assign(
            [
                'oxpaymentid' => $paymentId,
                'oxobjectid'  => $countryId,
                'oxtype'      => 'oxcountry'
            ]
        );
        $object2Paymentent->save();
    }

    /**
     * @param string $paymentId
     * @param string $deliverySetId
     * @return void
     * @throws \Exception
     */
    protected function assignPaymentToDelivery(string $paymentId, string $deliverySetId): void
    {
        $object2Paymentent = oxNew(EshopBaseModel::class);
        $object2Paymentent->init('oxobject2payment');
        $object2Paymentent->assign(
            [
                'oxpaymentid' => $paymentId,
                'oxobjectid'  => $deliverySetId,
                'oxtype'      => 'oxdelset'
            ]
        );
        $object2Paymentent->save();
    }

    /**
     * @param string $paymentId
     * @param array $definitions
     * @return void
     * @throws \Exception
     */
    protected function createPaymentMethod(string $paymentId, array $definitions): void
    {
        /** @var EshopModelPayment $paymentModel */
        $paymentModel = oxNew(EshopModelPayment::class);
        $paymentModel->setId($paymentId);

        $activeCountries = $this->getActiveCountries();
        $iso2LanguageId = array_flip($this->getLanguageIds());

        $active = (
                empty($definitions['countries']) ||
                0 < count(array_intersect($definitions['countries'], $activeCountries))
            ) &&
            $definitions['active'] === true;
        $paymentModel->assign(
            [
               'oxactive' => (int) $active,
               'oxfromamount' => (int) $definitions['constraints']['oxfromamount'],
               'oxtoamount' => (int) $definitions['constraints']['oxtoamount'],
               'oxaddsumtype' => (string) $definitions['constraints']['oxaddsumtype']
            ]
        );
        $paymentModel->save();

        foreach ($definitions['descriptions'] as $langAbbr => $data) {
            if (!isset($iso2LanguageId[$langAbbr])) {
                continue;
            }
            $paymentModel->loadInLang($iso2LanguageId[$langAbbr], $paymentModel->getId());
            $paymentModel->assign(
                [
                    'oxdesc' => $data['desc'],
                    'oxlongdesc' => $data['longdesc']
                ]
            );
            $paymentModel->save();
        }
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function ensureStaticContents(): void
    {
        foreach (UnzerDefinitions::getUnzerStaticContents() as $content) {
            $loadId = $content['oxloadid'];
            if (!$this->needToAddContent($loadId)) {
                continue;
            }

            foreach ($this->getLanguageIds() as $langId => $langAbbr) {
                $contentModel = $this->getContentModel($loadId, $langId);
                $contentModel->assign(
                    [
                        'oxloadid'  => $loadId,
                        'oxactive'  => $content['oxactive'],
                        'oxtitle'   => isset($content['oxtitle_' . $langAbbr]) ?
                            $content['oxtitle_' . $langAbbr] :
                            '',
                        'oxcontent' => isset($content['oxcontent_' . $langAbbr]) ?
                            $content['oxcontent_' . $langAbbr] :
                            '',
                    ]
                );
                $contentModel->save();
            }
        }
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function createRdfa(): void
    {
        foreach (UnzerDefinitions::getUnzerRdfaDefinitions() as $oxId => $rdfaDefinitions) {
            $this->assignPaymentToRdfa(
                $oxId,
                $rdfaDefinitions['oxpaymentid'],
                $rdfaDefinitions['oxrdfaid']
            );
        }
    }

    /**
     * @param string $oxId
     * @param string $paymentId
     * @param string $rdfaId
     * @return void
     * @throws \Exception
     */
    protected function assignPaymentToRdfa(string $oxId, string $paymentId, string $rdfaId): void
    {
        $object2Paymentent = oxNew(EshopBaseModel::class);
        $object2Paymentent->init('oxobject2payment');
        $object2Paymentent->assign(
            [
                'oxid'        => $oxId,
                'oxpaymentid' => $paymentId,
                'oxobjectid'  => $rdfaId,
                'oxtype'      => 'rdfapayment'
            ]
        );
        $object2Paymentent->save();
    }

    /**
     * @param string $ident
     * @return bool
     */
    protected function needToAddContent(string $ident): bool
    {
        $content = oxNew(EshopModelContent::class);
        if ($content->loadByIdent($ident)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $ident
     * @param int $languageId
     * @return EshopModelContent
     */
    protected function getContentModel(string $ident, int $languageId): EshopModelContent
    {
        $content = oxNew(EshopModelContent::class);
        if ($content->loadByIdent($ident)) {
            $content->loadInLang($languageId, $content->getId());
        }

        return $content;
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getActiveDeliverySetIds(): array
    {
        /** @var array $result */
        $result = null;

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->queryBuilderFactory->create();
        $resultDb = $queryBuilder
            ->select('oxid')
            ->from('oxdeliveryset')
            ->where('oxactive = 1')
            ->execute();

        if ($resultDb instanceof Result) {
            $fromDb = $resultDb->fetchAllAssociative();
            /** @var array $row */
            foreach ($fromDb as $row) {
                $result[$row['oxid']] = $row['oxid'];
            }
        }

        return $result;
    }

    /**
     * get the language-IDs
     */
    protected function getLanguageIds(): array
    {
        return EshopRegistry::getLang()->getLanguageIds();
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getActiveCountries(): array
    {
        $result = [];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->queryBuilderFactory->create();
        $resultDb = $queryBuilder
            ->select('oxid, oxisoalpha2')
            ->from('oxcountry')
            ->where('oxactive = 1')
            ->execute();

        if ($resultDb instanceof Result) {
            $fromDb = $resultDb->fetchAllAssociative();
            /** @var array $row */
            foreach ($fromDb as $row) {
                $result[$row['oxid']] = $row['oxisoalpha2'];
            }
        }

        return $result;
    }
}
