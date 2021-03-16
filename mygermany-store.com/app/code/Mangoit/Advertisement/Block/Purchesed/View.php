<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Advertisement\Block\Purchesed;

/*
 * Webkul Marketplace Seller Collection Block
 */
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $session;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpAdverHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $ownedAdsCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Framework\Registry                      $registry
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $session
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        Customer $customer,
        Session $session,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpAdvertisementManager\Helper\Data $mpAdverHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->Customer = $customer;
        $this->Session = $session;
        $this->_coreRegistry = $registry;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->mpAdverHelper = $mpAdverHelper;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
    }

    public function getOwnedAdsCollection()
    {
        // echo "<pre>";
        if($this->marketplaceHelper->isSeller()) {
            $sellerId = $this->marketplaceHelper->getCustomerId();
            $model = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory');
            if(!$this->ownedAdsCollection) {
                $this->ownedAdsCollection = $model->create()->getCollection()
                    ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                    ->addFieldToFilter('store_id', $this->mpAdverHelper->getCurrentStoreId());
            }
            return $this->ownedAdsCollection;
        }
    }

    /**
     * _prepareLayout prepare pager for rules list
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOwnedAdsCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'owned.adblocks.list.pager'
            )->setCollection(
                $this->getOwnedAdsCollection()
            );
            $this->setChild('pager', $pager);
            // $this->getOwnedAdsCollection();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
