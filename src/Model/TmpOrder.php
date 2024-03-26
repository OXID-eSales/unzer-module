<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\Unzer\Model;


use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSolutionCatalysts\Unzer\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

    public function prepareOrderForJson($oOrder)
    {
        $oConfig = $this->getConfig();
        $oOrderArticles = $oOrder->getOrderArticles();
        $completeOrder['order'] = $oOrder;
        $completeOrder['orderArticles'] = $oOrderArticles->getArray();
        $serializedOrder = serialize($completeOrder);
        $base64Order = base64_encode($serializedOrder);
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
     * @param $unzerOrderNr
     * @return false|mixed
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTmpOrderByUnzerId($unzerOrderNr)
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
        $blocksData = $queryBuilder->execute();
        $result = $blocksData->fetch();
        if ($result) {
            return $result;
        }
        return false;
    }


}
