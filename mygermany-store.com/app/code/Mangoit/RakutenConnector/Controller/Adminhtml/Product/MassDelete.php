<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;
use Mangoit\RakutenConnector\Controller\Adminhtml\Product;

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
        \Mangoit\RakutenConnector\Helper\Data $helper
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
