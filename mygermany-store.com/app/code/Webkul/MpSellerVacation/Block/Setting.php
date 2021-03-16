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
namespace Webkul\MpSellerVacation\Block;

/**
 * MpSellerVacation block.
 *
 * @author Webkul Software
 */
class Setting extends \Magento\Framework\View\Element\Template
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

    protected $vacationhelper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context                        $context
     * @param \Webkul\MpSellerVacation\Model\ResourceModel\Vacation\CollectionFactory $vacationFactory
     * @param \Magento\Customer\Model\Session                                         $customerSession
     * @param \Webkul\Marketplace\Helper\Data                                         $marketplaceHelper
     * @param \Magento\Framework\Message\ManagerInterface                             $messageManager
     * @param DateTime                                                                $date
     * @param Store                                                                   $store
     * @param array                                                                   $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpSellerVacation\Model\ResourceModel\Vacation\CollectionFactory $vacationFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpSellerVacation\Helper\Data $helper,
        array $data = []
    ) {

        $this->_date = $context->getLocaleDate();
        $this->vacationhelper = $helper;
        $this->_messageManager = $messageManager;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_vacationFactory = $vacationFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        parent::_construct();
    }


    /**
     * get logged in seller vacation details
     *
     * @return Webkul\MpSellerVacation\Model\Vacation
     */
    public function getMpsellervacation()
    {
        $sellerId = $this->getMarketplaceHelper()->getCustomerId();
        $vacationsdata = $this->_vacationFactory->create()->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        foreach ($vacationsdata as $data) {
            return $data;
        }
        return '';
    }
    /**
     * return marketplace helper.
     *
     * @return Webkul\Marketplace\Helper\Data
     */
    public function getMarketplaceHelper()
    {
        return $this->_marketplaceHelper;
    }

    /**
     * [getStoreId return current store id].
     *
     * @return int [store id]
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }

    /**
     * [getWebsiteId return current store id].
     *
     * @return int [store id]
     */
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * get Current time.
     *
     * @return date
     */
    public function getCurrentDate()
    {
        return $this->_date->date()->format('Y-m-d H:i:s');
    }

    public function getLocalDate($data)
    {
      if ($data) {
          if($this->vacationhelper->getVacationMode() == "add_to_cart_disable") {
            return date_format(date_create($data), "Y-m-d H:i:s");
          } else {
            return date_format(date_create($data), "Y-m-d");
          }
      }
      return $data;
    }

    /**
     * @return string
     */
    public function getConfigTimeZone()
    {
        return $this->_localeDate->getConfigTimezone();
    }

    /**
     * @return integer
     */
    public function getUtcOffset($date)
    {
        return timezone_offset_get(new \DateTimeZone($this->_localeDate->getConfigTimezone()), new \DateTime($date));
    }
}
