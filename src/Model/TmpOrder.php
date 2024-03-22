<?php
/**
 * This file is part of a maexware solutions module.
 *
 * This maexware solutions module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This maexware solutions module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with maexware wirecard module. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link https://www.maexware-solutions.de
 * @author Danny Zimmer <danny.zimmer@maexware-solutions.de>
 * @copyright (C) maexware solutions GmbH 2020
 */

namespace OxidSolutionCatalysts\Unzer\Model;


use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class TmpOrder extends BaseModel
{

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
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getTmpOrderByUnzerId($unzerOrderNr) {
        $container = ContainerFactory::getInstance()->getContainer();
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
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
