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
namespace  Mangoit\Vendorcommission\Block\Adminhtml\Customer\Edit\Tab;

class Commission extends \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Commission
{

    const COMM_TEMPLATE = 'Mangoit_Vendorcommission::commission.phtml';

    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Backend\Block\Widget\Context     $context
     * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->customerEdit = $customerEdit;
        parent::__construct($registry, $context, $customerEdit, $data);
    }
    
    public function getObjectManager(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::COMM_TEMPLATE);
        }

        return $this;
    }

    public function getCommission()
    {
        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSalesPartnerCollection();
        if (count($collection)) {
            foreach ($collection as $value) {
                $rowcom = $value->getCommissionRate();
            }
        } else {
            $rowcom = $this->getObjectManager()->create(
                'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
            )->getConfigCommissionRate();
        }
        $tsale = 0;
        $tcomm = 0;
        $tact = 0;
        $collection1 = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSalesListCollection();
        foreach ($collection1 as $key) {
            $tsale += $key->getTotalAmount();
            $tcomm += $key->getTotalCommission();
            $tact += $key->getActualSellerAmount();
        }

        return [
            'total_sale' => $tsale,
            'total_comm' => $tcomm,
            'actual_seller_amt' => $tact,
            'current_val' => $rowcom,
        ];
    }

    public function getCurrencySymbol()
    {
        $currencySymbol = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getCurrencySymbol();

        return $currencySymbol;
    }

    public function getAttributesType()
    {
        $attributes = $this->getObjectManager()->create('Mangoit\Vendorcommission\Helper\Data')->getCustomAttributeOption();
        return $attributes;        
    }

    public function getCustomerId()
    {
        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSalesPartnerCollection();
        return $collection;
    }

    public function getSallerCollection()
    {
        $collection = $this->getObjectManager()->create('Webkul\Marketplace\Model\Saleperpartner');
        return $collection;
    }
}
