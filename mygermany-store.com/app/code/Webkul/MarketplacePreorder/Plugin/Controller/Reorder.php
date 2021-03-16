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
namespace Webkul\MarketplacePreorder\Plugin\Controller;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\Registry;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Framework\App\Action;

class Reorder
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;
    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    private $_preorderHelper;
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    protected $resultRedirectFactory;

    protected $messageManager;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     */
    public function __construct(
        Action\Context $context,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        OrderLoaderInterface $orderLoader,
        Registry $registry
    ) {
        $this->_request = $context->getRequest();
        $this->messageManager = $context->getMessageManager();
        $this->_preorderHelper = $preorderHelper;
        $this->orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Action for reorder
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function aroundExecute(\Magento\Sales\Controller\AbstractController\Reorder $subject, \Closure $proceed)
    {
        $result = $this->orderLoader->load($this->_request);
        $order = $this->_coreRegistry->registry('current_order');
        $items = $order->getItemsCollection();
        $resultRedirect = $this->resultRedirectFactory->create();
        foreach ($items as $item) {
            if ($item->getSku()=="preorder_complete") {
                $this->messageManager->addNotice(__('You Can not reorder Complete Preorder product.'));
                return $resultRedirect->setPath('*/*/history');
            }
        }
        $this->_coreRegistry->unregister('current_order');
        $returnValue = $proceed();
        return $returnValue;
    }
}
