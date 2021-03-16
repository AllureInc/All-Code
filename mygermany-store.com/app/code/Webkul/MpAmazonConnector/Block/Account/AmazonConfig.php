<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Webkul\MpAmazonConnector\Model\AccountsFactory;
use Webkul\MpAmazonConnector\Model\Config\Source;

class AmazonConfig extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountsFactory $accountFactory,
        Source\AmazonMarketplace $amazonMarketplace,
        Source\AllStoreList $allStoreList,
        Source\AllWebsiteList $allWebsiteList,
        Source\CategoriesList $categoriesList,
        Source\MarketplaceSellers $sellers,
        Source\ImportType $importType,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSet,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->accountFactory = $accountFactory;
        $this->amazonMarketplace = $amazonMarketplace;
        $this->attributeSet = $attributeSet;
        $this->allStoreList = $allStoreList;
        $this->allWebsiteList = $allWebsiteList;
        $this->categoriesList = $categoriesList;
        $this->importType = $importType;
        parent::__construct($context, $data);
    }

    public function getCategoryList()
    {
        return $this->categoriesList->toOptionArray();
    }

    public function getAllStoreList()
    {
        return $this->allStoreList->toOptionArray();
    }

    public function getProductOpeation()
    {
        return $this->importType->toOptionArray();
    }

    /**
     * getSellerAmzDetail
     * @return EbaySellerAccountFactory
     */
    public function getSellerAmzDetail()
    {
        $sellerId = $this->_customerSession->getCustomerId();
        $amzAccount = $this->accountFactory->create();
        $amzAccountCol = $amzAccount->getCollection()->addFieldToFilter('seller_id', $sellerId);
        foreach ($amzAccountCol as $amzDet) {
            return $amzDet;
        }
        return $amzAccount;
    }

    /**
     * getConfigSaveAction
     * @return string
     */
    public function getConfigSaveAction()
    {
        return $this->getUrl('mpamazonconnect/account/save', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getAmzCountryOrigion
     * @return array
     */
    public function getAmzMarketplaces()
    {
        return $this->amazonMarketplace->toArray();
    }

    /**
     * get attribute sets
     *
     * @return array
     */
    public function getAttributeSets()
    {
        return $this->attributeSet->toOptionArray();
    }
}
