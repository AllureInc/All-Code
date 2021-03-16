<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Observer;

use Magento\Framework\Event\ObserverInterface;

use Magento\Eav\Model\ResourceModel\Entity\Attribute;

/**
 * Product Load Before  Observer.
 */
class ProductLoadBefore implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var AttributeFactory
     */
    protected $_eavEntity;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param CollectionFactory                           $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MarketplacePreorder\Helper\Data $helper,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Attribute $eavEntity
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_state = $state;
        $this->_eavEntity = $eavEntity;
        $this->_helper = $helper;
        $this->_date = $date;
    }

    /**
     * Product delete after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $attributeId = $this->_eavEntity->getIdByCode('catalog_product', 'wk_mppreorder_qty');
            if ($attributeId !== '' && $attributeId !== null && $attributeId !== 0) {
                $attribute = $this->_objectManager->create(
                    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
                )->load($attributeId);
            }
            if ((int) $this->_helper->getConfigData('mppreorder_qty') == 1) {
                $attribute->setIsVisible(true)->save();
            } else {
                $attribute->setIsVisible(false)->save();
            }
        }
    }
}
