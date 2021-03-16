<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

class PaymentInfo extends \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab\PaymentInfo
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $customerEdit;

    const COMM_TEMPLATE = 'Mangoit_Marketplace::customer/paymentinfo.phtml';

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

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::COMM_TEMPLATE);
        }

        return $this;
    }

    public function getPaymentInfo()
    {
        $paymentInfo = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getPaymentMode();

        return $paymentInfo;
    }
}
