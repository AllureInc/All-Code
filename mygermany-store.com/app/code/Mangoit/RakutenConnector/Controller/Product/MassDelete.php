<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;
use Mangoit\RakutenConnector\Helper\Data;

class MassDelete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        Data $helper,
        ProductMapRepositoryInterface $productMapRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->productMapRepository = $productMapRepository;
        $this->_resultPageFactory = $_resultPageFactory;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * MpAmazonConnector Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $params = $this->getRequest()->getParams();
        $sellerId = $this->helper->getCustomerId();
        $this->registry->register('isSecureArea', true);
        if (isset($params['rktn_mass_del']) && !empty($params['rktn_mass_del'])) {
            $collection = $this->productMapRepository->getByIds($params['rktn_mass_del'], $sellerId);
            if ($collection->getSize()) {
                foreach ($collection as $mappedRecord) {
                    $this->_deleteMageProduct($mappedRecord->getMagentoProId());
                    $mappedRecord->delete();
                }
                $this->messageManager->addSuccess(
                    __("Product successfully deleted.")
                );
            } else {
                $this->messageManager->addError(
                    __("You don'\t have authorization to access the product.")
                );
            }
        } else {
            $this->messageManager->addError(
                __("Invalid request.")
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl(
            $this->_url->getUrl('rakutenconnect/product/index')
        );
    }

    /**
     * delete magento product
     *
     * @param int $mageProductId
     * @return void
     */
    private function _deleteMageProduct($mageProductId)
    {
        $product = $this->productRepository->getById($mageProductId);
        $this->productRepository->delete($product);
    }
}
