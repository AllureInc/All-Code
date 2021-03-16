<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Product;

class MassDelete extends Product
{
    /**
     * @var ProductMapRepositoryInterface
     */
    private $productMapRepository;

    /**
     * @param Context                       $context
     * @param ProductmapRepositoryInterface $productMapRepositoryInterface
     */
    public function __construct(
        Context $context,
        ProductMapRepositoryInterface $productMapRepository,
        \Webkul\MpAmazonConnector\Helper\Data $helper
    ) {
        $this->productMapRepository = $productMapRepository;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $sellerId = $this->helper->getCustomerId($params['id']);
        $collection = $this->productMapRepository
                    ->getByIds($params['productEntityIds'], $sellerId);

        $productMapCount = 0;
        foreach ($collection as $productMap) {
            $productMap->delete();
            ++$productMapCount;
        }
        $this->messageManager->addSuccess(
            __("A total of %1 record(s) have been deleted.", $productMapCount)
        );

        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath(
            '*/accounts/edit',
            [
                'id'=>$params['id'],
                'active_tab' => 'product_sync'
            ]
        );
    }
}
