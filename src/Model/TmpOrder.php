<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\Unzer\Model;

use Doctrine\DBAL\Driver\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\Result;
use Exception;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Service\FlexibleSerializer;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Application\Model\Order as CoreOrderModel;

class TmpOrder extends BaseModel
{
    use ServiceContainer;

    /**
     * Class constructor, initiates parent constructor.
     * @codeCoverageIgnore
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('oscunzertmporder');
    }

    public function save()
    {
        return parent::save();
    }

    public function saveTmpOrder(CoreOrderModel $oOrder): void
    {
        $oConfig = Registry::getConfig();
        $oOrderArticles = $oOrder->getOrderArticles();
        $completeOrder = [
            'order'         => $oOrder,
            'orderArticles' => $oOrderArticles->getArray()
        ];
        $flexibleSerializer = $this->getServiceFromContainer(FlexibleSerializer::class);
        $serializedOrder = $flexibleSerializer->safeSerialize($completeOrder);
        $base64Order = base64_encode($serializedOrder);

        /** @var Order $oOrder */
        $oxId = $this->getOxIdFromTmpOrder($oOrder->getId(), $oOrder->getUnzerOrderNr());
        if ($oxId) {
            $this->load($oxId);
        }

        /** @var Order $oOrder */
        $this->assign([
            'oxshopid'       => $oConfig->getShopId(),
            'oxorderid'      => $oOrder->getId(),
            'oxunzerordernr' => $oOrder->getUnzerOrderNr(),
            'tmporder'       => $base64Order,
            'status'         => 'NOT_FINISHED',
            'timestamp'      => date('Y-m-d H:i:s')
        ]);
        $this->save();
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getOxIdFromTmpOrder(string $orderOxId, string $unzerOrderNr): string
    {
        /** @var \OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactory $queryBuilderFactory */
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder
            ->select('oxid')
            ->from('oscunzertmporder')
            ->where('oxorderid = :oxorderid')
            ->andWhere('oxunzerordernr = :oxunzerordernr')
            ->setParameters(
                [
                    'oxorderid'      => $orderOxId,
                    'oxunzerordernr' => $unzerOrderNr
                ]
            );
        /** @var Result $blocksData */
        $blocksData = $queryBuilder->execute();
        $result = is_a($blocksData, Result::class) ? $blocksData->fetchAssociative() : false;
        return isset($result['oxid']) && is_string($result['oxid']) ? $result['oxid'] : '';
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getTmpOrderByUnzerId(string $unzerOrderNr): array
    {
        /** @var \OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactory $queryBuilderFactory */
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder
            ->select('*')
            ->from('oscunzertmporder')
            ->where('oxunzerordernr = :oxunzerordernr')
            ->andWhere('status = "NOT_FINISHED"')
            ->orderBy('timestamp', 'ASC')
            ->setParameters(
                ['oxunzerordernr' => $unzerOrderNr]
            );
        /** @var Result $blocksData */
        $blocksData = $queryBuilder->execute();
        $result = is_a($blocksData, Result::class) ? $blocksData->fetchAssociative() : false;
        return is_array($result) ? $result : [];
    }

    /**
     * @throws DBALException
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTmpOrderByOxOrderId(string $oxSessionOrderId): ?CoreOrderModel
    {
        /** @var \OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactory $queryBuilderFactory */
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder
            ->select('*')
            ->from('oscunzertmporder')
            ->where('oxorderid = :oxorderid')
            ->orderBy('timestamp', 'ASC')
            ->setParameters(
                ['oxorderid' => $oxSessionOrderId]
            );
        /** @var Result $rawRes */
        $rawRes = $queryBuilder->execute();
        $result = $rawRes->fetchAssociative();

        if (is_array($result) && isset($result['tmporder']) && is_string($result['tmporder'])) {
            $tmpOrder = base64_decode($result['tmporder']);
            $flexibleSerializer = $this->getServiceFromContainer(FlexibleSerializer::class);
            $result = $flexibleSerializer->safeUnserialize($tmpOrder, [CoreOrderModel::class, Field::class]);
            if (is_array($result) && isset($result['order']) && is_object($result['order'])) {
                /** @var CoreOrderModel $order */
                $order = $result['order'];
                return $order;
            }
        }

        return null;
    }
}
