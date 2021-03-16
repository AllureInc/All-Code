<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Store and language switcher block
 */
namespace Kerastase\Core\Block;


use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Switcher block
 *
 * @api
 * @since 100.0.2
 */
class Switcher extends \Magento\Framework\View\Element\Template
{

    const WEBSITE_COUNTRY_PATH = 'general/store_information/country_id';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $_countryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = []

    ) {
        $this->_countryFactory = $countryFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);

    }


    /**
     * @param $path
     * @param null $websiteId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteCountry( $websiteId = null)
    {
        if ($websiteId == null || $websiteId == '') {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $countryCode = $this->scopeConfig->getValue(
            self::WEBSITE_COUNTRY_PATH,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId);

        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();

    }

}
