<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\Product;

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
        ProductMapRepositoryInterface $productMapRepository
    ) {
        $this->productMapRepository = $productMapRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $collection = $this->productMapRepository
                    ->getCollectionByIds($params['productEntityIds']);

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
                'id'=>$params['account_id'],
                'active_tab' => 'product_sync'
            ]
        );
    }
}
