<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Block\Vacation;

use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * MpSellerVacation block.
 *
 * @author Webkul Software
 */
class Status extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var Webkul\MpSellerVacation\Model\ResourceModel\Vacation\CollectionFactory
     */
    protected $_vacationFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Magento\Framework\Registry.
     *
     * @var [type]
     */
    protected $_registry;

    /**
     * @param Context                                    $context
     * @param array                                      $data
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpSellerVacation\Model\ResourceModel\Vacation\CollectionFactory $_vacationFactory,
        \Magento\Customer\Model\Session $_customerSession,
        \Webkul\Marketplace\Helper\Data $_marketplaceHelper,
        \Magento\Framework\Message\ManagerInterface $_messageManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $_registry,
        DateTime $date,
        array $data = []
    ) {

        $this->_objectManager = $objectManager;
        $this->_date = $date;
        $this->_messageManager = $_messageManager;
        $this->_marketplaceHelper = $_marketplaceHelper;
        $this->_vacationFactory = $_vacationFactory;
        $this->_customerSession = $_customerSession;
        $this->_registry = $_registry;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
    }
    /**
     * function to get seller vacation settings.
     *
     * @return \Webkul\MpSellerVacation\Model\ResourceModel\Vacation\Collection
     */
    public function getSellerVacationStatus()
    {
        $seller = $this->getProfileDetail();
        if ($seller) {
            $sellerId = $seller->getSellerId();
            $vacationsData = $this->_vacationFactory->create()
                ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
            if ($vacationsData->getSize()) {
                foreach ($vacationsData as $data) {
                    return $data;
                }
            }
        }

        return false;
    }

    /**
     * Get Seller Add to cart status.
     */
    public function getAddToCartStatus($vacation = false)
    {
        return $this->_objectManager->create('Webkul\MpSellerVacation\Helper\Data')->getVacationMode($vacation);
    }

    public function getAddToCartButtonLabel()
    {
        return $this->_objectManager->create('Webkul\MpSellerVacation\Helper\Data')->getStoreCloseLabel();
    }

    /**
     * Check if seller is on vacation.
     */
    public function getIsOnVacation($vacation)
    {
        return $this->_objectManager->create('Webkul\MpSellerVacation\Helper\Data')->checkDisableTime($vacation);
    }

    /**
     * Check if seller is on vacation but product enabled.
     */
    public function getVacationStatus($vacation)
    {
        return $this->_objectManager->create('Webkul\MpSellerVacation\Helper\Data')->isOnVacation($vacation);
    }

    /**
     * get seller profile details.
     *
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getProfileDetail()
    {
        $shopUrl = '';
        if ($this->_registry->registry('current_product')) {
            $shopUrl = $this->_objectManager
                ->create('Webkul\MpSellerVacation\Helper\Data')
                ->getSellerDataByProduct($this->_registry->registry('current_product')->getId());
        } else {
            if ($this->getRequest()->getActionName() == 'profile') {
                $shopUrl = $this->_marketplaceHelper->getProfileUrl();
                if (!$shopUrl) {
                    $shopUrl = $this->_request->getParam('shop');
                }
            } elseif ($this->getRequest()->getActionName() == 'collection') {
                $shopUrl = $this->_marketplaceHelper->getCollectionUrl();
                if (!$shopUrl) {
                    $shopUrl = $this->_request->getParam('shop');
                }
            } elseif ($this->getRequest()->getActionName() == 'location') {
                $shopUrl = $this->_marketplaceHelper->getLocationUrl();
                if (!$shopUrl) {
                    $shopUrl = $this->_request->getParam('shop');
                }
            } elseif ($this->getRequest()->getActionName() == 'feedback') {
                $shopUrl = $this->_marketplaceHelper->getFeedbackUrl();
                if (!$shopUrl) {
                    $shopUrl = $this->_request->getParam('shop');
                }
            } else {
                $shopUrl = $this->_request->getParam('shop');
            }
        }

        if ($shopUrl) {
            $data = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                ->getCollection()
                ->addFieldToFilter('shop_url', ['eq' => $shopUrl]);
            foreach ($data as $seller) {
                return $seller;
            }
        }

        return false;
    }
}
