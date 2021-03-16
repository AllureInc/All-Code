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
use Magento\Framework\App\RequestInterface;

class BeforeSaveProduct implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @param RequestInterface $request
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     */
    public function __construct(
        RequestInterface $request,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper
    ) {
        $this->_request = $request;
        $this->_preorderHelper = $preorderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_preorderHelper;
        $productId = 0;
        $preorderProductId = $helper->getPreorderCompleteProductId();
        $data = $this->_request->getParams();
        if (array_key_exists('id', $data)) {
            $productId = $data['id'];
        }
        if (!array_key_exists('is_admin', $data)) { // in case update product
            if ($productId == $preorderProductId) {
                $error = "You can not update 'Complete PreOrder' Product";
                throw new \Magento\Framework\Validator\Exception(__($error));
            }
        }
    }
}
