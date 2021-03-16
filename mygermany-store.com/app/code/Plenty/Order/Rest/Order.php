<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

use Plenty\Core\Rest\Client;
use Plenty\Order\Rest\Response\OrderDataBuilder;
use Plenty\Order\Helper\Data as Helper;
use Plenty\Order\Model\Logger;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Variation
 * @package Plenty\Item\Rest
 */
class Order extends AbstractOrder implements OrderInterface
{
    /**
     * Order constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param OrderDataBuilder $orderDataBuilder
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        OrderDataBuilder $orderDataBuilder
    ) {
        $this->_responseParser = $orderDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return OrderDataBuilder
     */
    public function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @param int $page
     * @param null $orderId
     * @param null $externalOrderId
     * @param null $referrerId
     * @param null $contactId
     * @param null $with
     * @param null $paymentStatus
     * @param null $updatedAtFrom
     * @param null $createdAtFrom
     * @param null $paidAtFrom
     * @param null $outgoingItemsBookedAtFrom
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchOrders(
        $page = 1,
        $orderId = null,
        $externalOrderId = null,
        $referrerId = null,
        $contactId = null,
        $with = null,
        $paymentStatus = null,
        $updatedAtFrom = null,
        $createdAtFrom = null,
        $paidAtFrom = null,
        $outgoingItemsBookedAtFrom = null
    ) : Collection
    {
        $params = ['page' => $page];
        $externalOrderId ? $params['externalOrderId'] = $externalOrderId : null;
        $referrerId ? $params['referrerId'] = $referrerId : null;
        $contactId ? $params['contactId'] = $contactId : null;
        $with ? $params['with'] = $with : null;
        $paymentStatus ? $params['paymentStatus'] = $paymentStatus : null;
        $updatedAtFrom ? $params['updatedAtFrom'] = $updatedAtFrom : null;
        $createdAtFrom ? $params['createdAtFrom'] = $createdAtFrom : null;
        $paidAtFrom ? $params['paidAtFrom'] = $paidAtFrom : null;
        $outgoingItemsBookedAtFrom ? $params['outgoingItemsBookedAtFrom'] = $outgoingItemsBookedAtFrom : null;

        try {
            $this->_api()
                ->get($this->_helper()->getOrderUrl($orderId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $referrerId
     * @param null $with
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchOrdersByReferrerId($referrerId, $with = null) : Collection
    {
        $params = ['referrerId' => $referrerId];
        $with ? $params['with'] = $with : null;
        try {
            $this->_api()
                ->get($this->_helper()->getOrderUrl() . '?' . http_build_query($params));

            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param $orderId
     * @param null $with
     * @return DataObject
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderById($orderId, $with = null) : DataObject
    {
        $params = [];
        $with ? $params['with'] = $with : null;
        try {
            if (empty($params)) {
                $this->_api()->get($this->_helper()->getOrderUrl($orderId));
            } else {
                $this->_api()
                    ->get($this->_helper()->getOrderUrl($orderId) . '?' . http_build_query($params));
            }

            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response->getFirstItem();
    }

    /**
     * @param $externalId
     * @param null $with
     * @return DataObject
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderByExternalOrderId($externalId, $with = null) : DataObject
    {
        $params = ['externalOrderId' => $externalId];
        $with ? $params['with'] = $with : null;
        try {
            $this->_api()
                ->get($this->_helper()->getOrderUrl() . '?' . http_build_query($params));

            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response->getFirstItem();
    }

    /**
     * @param $params
     * @param null $plentyOrderId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrder($params, $plentyOrderId = null) : ?array
    {
        try {
            if (null === $plentyOrderId) {
                $this->_api()->post($this->_helper()->getOrderUrl(), $params);
            } else {
                $this->_api()->put($this->_helper()->getOrderUrl($plentyOrderId), $params);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }
}