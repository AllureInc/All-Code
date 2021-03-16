<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For preparing customer's data
 */
namespace Cnnb\Gtm\Block\Data;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Block\DataLayer;
use Cnnb\Gtm\Model\Customer as CustomerModel;

class Customer extends Template
{
    /**
     * @var CustomerModel
     */
    protected $_customerModel;

    /**
     * @param Context $context
     * @param CustomerModel $customerModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerModel $customerModel,
        array $data = []
    ) {
        $this->_customerModel = $customerModel;
        parent::__construct($context, $data);
    }

    /**
     * Add product data to datalayer
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /** @var $tm DataLayer */
        $tm = $this->getParentBlock();
        $tm->addVariable('customer', $this->_customerModel->getCustomer());

        return $this;
    }
}
