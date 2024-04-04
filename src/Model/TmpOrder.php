<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\Result;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
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

    public function prepareOrderForJson(CoreOrderModel $oOrder): void
    {
        $oConfig = Registry::getConfig();
        $oOrderArticles = $oOrder->getOrderArticles();
        $completeOrder['order'] = $oOrder;
        $completeOrder['orderArticles'] = $oOrderArticles->getArray();
        $serializedOrder = serialize($completeOrder);
        $base64Order = base64_encode($serializedOrder);
        /** @var Order $oOrder */
        $this->assign(
            [
                'OXSHOPID'  => $oConfig->getShopId(),
                'OXORDERID'   => $oOrder->getId(),
                'OXUNZERORDERNR'  => $oOrder->getUnzerOrderNr(),
                'TMPORDER'  => $base64Order,
                'STATUS' => 'NOT_FINISHED',
                'TIMESTAMP' => date('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * @param int $unzerOrderNr
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getTmpOrderByUnzerId(int $unzerOrderNr): array
    {
        $queryBuilderFactory = $this->getServiceFromContainer(QueryBuilderFactoryInterface::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $queryBuilderFactory->create();
        $queryBuilder
            ->select('*')
            ->from('oscunzertmporder')
            ->where('OXUNZERORDERNR = :OXUNZERORDERNR')
            ->andWhere('STATUS = "NOT_FINISHED"')
            ->orderBy('timestamp', 'ASC')
            ->setParameters(
                ['OXUNZERORDERNR' => $unzerOrderNr]
            );
        /** @var Result $blocksData */
        $blocksData = $queryBuilder->execute();
        $result = is_a($blocksData, Result::class) ? $blocksData->fetchAssociative() : false;
        return is_array($result) ? $result : [];
    }
}
