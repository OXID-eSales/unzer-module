<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Service;

use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\Exception\InvalidThemeNameException;
use OxidEsales\Twig\TwigEngineConfigurationInterface;
use OxidSolutionCatalysts\Unzer\Core\UnzerDefinitions;
use OxidEsales\Eshop\Application\Model\Content as EshopModelContent;
use OxidEsales\Eshop\Application\Model\Payment as EshopModelPayment;
use OxidEsales\Eshop\Core\Model\BaseModel as EshopBaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class StaticContent
{
    private QueryBuilderFactoryInterface $queryBuilderFactory;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws \Exception
     */
    public function ensureUnzerPaymentMethods(): void
    {
        foreach (UnzerDefinitions::getUnzerDefinitions() as $paymentId => $paymentDefinitions) {
            $paymentMethod = oxNew(EshopModelPayment::class);
            if ($paymentMethod->load($paymentId)) {
                $this->updatePaymentToCountries($paymentId, $paymentDefinitions['countries']);
                $this->checkAndDeactivatePaymentMethod($paymentDefinitions, $paymentMethod);
                continue;
            }
            $this->createPaymentMethod($paymentId, $paymentDefinitions);
            $this->assignPaymentToCountries($paymentId, $paymentDefinitions['countries']);
            $this->assignPaymentToActiveDeliverySets($paymentId);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function ensureStaticContents(): void
    {
        $feEngine = $this->getFrontendEngine();
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
                        'oxtitle'   => $this->getContentBlockTitle($content, $langAbbr),
                        'oxcontent' => $this->getContentBlockContent($content, $langAbbr, $feEngine)
                    ]
                );
                $contentModel->save();
            }
        }
    }

    /**
     * @throws \Exception
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

    private function checkAndDeactivatePaymentMethod(array $paymentDefinition, EshopModelPayment $paymentMethod): void
    {
        // TODO fixme Access to an undefined property OxidEsales\Eshop\Application\Model\Payment::$oxpayments__oxactive.
        /** @phpstan-ignore-next-line */
        if (!$paymentDefinition['active'] && $paymentMethod->oxpayments__oxactive->value == 1) {
            /** @phpstan-ignore-next-line */
            $paymentMethod->oxpayments__oxactive->value = 0;
            $paymentMethod->save();
        }
    }

    private function getAssignToCountries(array $paymentCountries): array
    {
        $activeCountries = array_flip($this->getActiveCountries());
        $assignToCountries = [];
        foreach ($paymentCountries as $countryIsoAlpha2) {
            if (isset($activeCountries[strtoupper($countryIsoAlpha2)])) {
                $assignToCountries[strtoupper($countryIsoAlpha2)] = $activeCountries[strtoupper($countryIsoAlpha2)];
            }
        }
        $assignToCountries = empty($assignToCountries) ? $activeCountries : $assignToCountries;
        return $assignToCountries;
    }

    private function getAssignedCountriesFromPayment(string $paymentId): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $statement = $queryBuilder
            ->select('*')
            ->from('oxobject2payment')
            ->where('oxpaymentid = :oxpaymentid')
            ->andWhere('oxtype = "oxcountry"')
            ->setParameters([':oxpaymentid' => $paymentId]);

        $result = $statement->execute();
        $assignedCountries = [];
        if ($result instanceof Result) {
            $array = $result->fetchAllAssociative();
            foreach ($array as $obj2payment) {
                $assignedCountries[] = $obj2payment['OXOBJECTID'];
            }
        }
        return $assignedCountries;
    }

    private function updatePaymentToCountries(string $paymentId, array $countries): void
    {
        $assignToCountries = $this->getAssignToCountries($countries);
        $assignedCountries = $this->getAssignedCountriesFromPayment($paymentId);

        $toRemove = array_diff($assignedCountries, $assignToCountries);
        if (!empty($toRemove)) {
            foreach ($toRemove as $countryId) {
                $this->removePaymentFromCountry($paymentId, $countryId);
            }
        }
    }

    private function assignPaymentToActiveDeliverySets(string $paymentId): void
    {
        $deliverySetIds = $this->getActiveDeliverySetIds();
        foreach ($deliverySetIds as $deliverySetId) {
            $this->assignPaymentToDelivery($paymentId, $deliverySetId);
        }
    }

    private function assignPaymentToCountries(string $paymentId, array $countries): void
    {
        $assignToCountries = $this->getAssignToCountries($countries);

        foreach ($assignToCountries as $countryId) {
            $this->assignPaymentToCountry($paymentId, $countryId);
        }
    }

    private function assignPaymentToCountry(string $paymentId, string $countryId): void
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

    private function removePaymentFromCountry(string $paymentId, string $countryId): void
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $statement = $queryBuilder
            ->delete('oxobject2payment')
            ->where('oxpaymentid = :oxpaymentid')
            ->andWhere('oxobjectid = :oxobjectid')
            ->andWhere('oxtype = "oxcountry"')
            ->setParameters([
                ':oxpaymentid' => $paymentId,
                ':oxobjectid' => $countryId
            ]);
        $statement->execute();
    }

    private function assignPaymentToDelivery(string $paymentId, string $deliverySetId): void
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

    private function createPaymentMethod(string $paymentId, array $definitions): void
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

    private function assignPaymentToRdfa(string $oxId, string $paymentId, string $rdfaId): void
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

    private function needToAddContent(string $ident): bool
    {
        $content = oxNew(EshopModelContent::class);
        if ($content->loadByIdent($ident)) {
            return true;
        }
        return true;
    }

    private function getContentModel(string $ident, int $languageId): EshopModelContent
    {
        $content = oxNew(EshopModelContent::class);
        if ($content->loadByIdent($ident)) {
            $content->loadInLang($languageId, $content->getId());
        }

        return $content;
    }

    private function getActiveDeliverySetIds(): array
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

    private function getLanguageIds(): array
    {
        return EshopRegistry::getLang()->getLanguageIds();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function getActiveCountries(): array
    {
        $result = [];

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

    private function getContentBlockTitle(array $content, string $langAbbr): string
    {
        return $content['oxtitle_' . $langAbbr] ?? '';
    }

    private function getContentBlockContent(array $content, string $langAbbr, string $feEngine): string
    {
        if (isset($content['oxcontent_' . $langAbbr][$feEngine])) {
            return $content['oxcontent_' . $langAbbr][$feEngine];
        }

        return $content['oxcontent_' . $langAbbr] ?? '';
    }

    private function getFrontendEngine(): string
    {
        $container = ContainerFactory::getInstance()->getContainer();

        if ($container->has('OxidEsales\Twig\TwigEngineConfigurationInterface')) {
            return 'twig';
        }

        if ($container->has('OxidEsales\Smarty\SmartyEngineInterface')) {
            return 'smarty';
        }

        return 'twig';
    }
}
