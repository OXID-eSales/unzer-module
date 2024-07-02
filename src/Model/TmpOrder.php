<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use Doctrine\DBAL\Driver\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\Result;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use OxidEsales\Eshop\Application\Model\Order as CoreOrderModel;
use Exception;

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

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function saveTmpOrder(CoreOrderModel $oOrder): void
    {
        $oConfig = Registry::getConfig();
        $oOrderArticles = $oOrder->getOrderArticles();
        $completeOrder = [
            'order'         => $oOrder,
            'orderArticles' => $oOrderArticles->getArray()
        ];
        $serializedOrder = serialize($completeOrder);
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
     * @param string $orderOxId
     * @param int $unzerOrderNr
     * @return string $oxId
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getOxIdFromTmpOrder(string $orderOxId, int $unzerOrderNr): string
    {
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        /** @var QueryBuilder $queryBuilder */
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
     * @param string $unzerOrderNr
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getTmpOrderByUnzerId(string $unzerOrderNr): array
    {
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        /** @var QueryBuilder $queryBuilder */
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

    public function getTmpOrderByOxOrderId(string $oxSessionOrderId): ?CoreOrderModel
    {
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder
            ->select('*')
            ->from('oscunzertmporder')
            ->where('oxorderid = :oxorderid')
            ->orderBy('timestamp', 'ASC')
            ->setParameters(
                ['oxorderid' => $oxSessionOrderId]
            );
        /** @var Result $blocksData */
        $result = $queryBuilder->execute()->fetchAssociative();

        if (is_array($result) && isset($result['TMPORDER']) && is_string($result['TMPORDER'])) {
            $tmpOrder = $result['TMPORDER'];
            $result = unserialize(base64_decode($tmpOrder));
            if (is_array($result) && isset($result['order']) && is_object($result['order'])) {
                /** @var \OxidSolutionCatalysts\Unzer\Model\Order $order */
                $order = $result['order'];
                return $order;
            }
        }

        return null;
    }
}
