<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Rest;

use Magento\Framework\Data\CollectionFactory;
use Plenty\Core\Rest\AbstractClient;
use Plenty\Core\Rest\Client;
use Plenty\Stock\Rest\Response\StockDataBuilder;
use Plenty\Stock\Helper\Data as Helper;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Plenty\Stock\Model\Logger;

/**
 * Class Stock
 * @package Plenty\Stock\Model\Rest
 */
class Stock extends AbstractClient
    implements StockInterface
{
    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Stock constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param StockDataBuilder $responseParser
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        StockDataBuilder $responseParser,
        Helper $helper,
        Logger $logger
    ) {
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_responseParser = $responseParser;
        parent::__construct($httpClientFactory, $dataCollectionFactory);
    }

    /**
     * @return Helper
     */
    protected function _helper()
    {
        return $this->_helper;
    }

    /**
     * @return Logger
     */
    protected function _logger()
    {
        return $this->_logger;
    }

    /**
     * @return StockDataBuilder
     */
    protected function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getDebugLevel()
    {
        return $this->_helper()->getDebugLevel();
    }

    /**
     * @param $message
     * @param array $context
     * @param bool $forceDebug
     * @return $this|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _logResponse($message, array $context = [], $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper()->isDebugOn()) {
            return false;
        }

        $this->_logger()->debug($message, $context);
        return $this;
    }

    /**
     * @param $message
     * @param $method
     * @param bool $forceDebug
     * @return $this|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _logError($message, $method, $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper()->isDebugOn()) {
            return false;
        }

        $this->_logger()->error([$method => $message]);
        return $this;
    }

    /**
     * @param $variationId
     * @param $warehouseId
     * @return Collection|mixed
     * @throws LocalizedException
     */
    public function getStockByItem($variationId, $warehouseId)
    {
        try {
            $this->_api()->get($this->_helper()->getListStockPerItemUrl($variationId, $warehouseId));
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
     * @param null $page
     * @param null $variationId
     * @param null $warehouseId
     * @param null $updatedAtFrom
     * @param null $updatedAtTo
     * @param null $itemsPerPage
     * @return Collection
     * @throws LocalizedException
     */
	public function getSearchStock(
        $page = null,
        $variationId = null,
        $warehouseId = null,
		$updatedAtFrom = null,
		$updatedAtTo = null,
        $itemsPerPage = null
	): Collection {
        $params = [];
        $page ? $params['page'] = $page : null;
	    $variationId ? $params['variationId'] = $variationId : null;
		$updatedAtFrom ? $params['updatedAtFrom'] = $updatedAtFrom : null;
		$updatedAtTo ? $params['updatedAtTo'] = $updatedAtTo : null;
        $itemsPerPage ? $params['itemsPerPage'] = $itemsPerPage : null;

        try {
            $this->_api()->get($this->_helper()->getListStockUrl($warehouseId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
	}
}