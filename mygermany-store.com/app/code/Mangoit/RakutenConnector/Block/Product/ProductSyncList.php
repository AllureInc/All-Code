<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductSyncList extends \Magento\Framework\View\Element\Template
{
    /**
     * Mangoit\RakutenConnector\Model\ResourceModel\Productmap\CollectionFactory
     * @var $_mapedProcollection
     */
    protected $mappedProduct;

    /**
     * @param Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface $mappedProductRepository,
        ProductRepositoryInterface $productRepository,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        array $data = []
    ) {
        $this->mappedProductRepository = $mappedProductRepository;
        $this->customerSession = $customerSession;
        $this->imageHelper = $context->getImageHelper();
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * getMappedProducts
     * @return array
     */
    public function getMappedProducts()
    {
        if (!($sellerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->mappedProduct) {
            $this->mappedProduct = $this->mappedProductRepository->getByAccountId($sellerId)->setOrder('entity_id', 'desc');
            $filter = $this->getRequest()->getParams();
            if (isset($filter['s'])) {
                $this->mappedProduct = $this->mappedProduct->addFieldToFilter('name', ['like'=>'%'.$filter['s'].'%']);
            }
        }
        return $this->mappedProduct;
    }

    /**
     * getImportUrl
     * @return string
     */
    public function getImportUrl()
    {
        return $this->getUrl('rakutenconnect/product/import', ['_secure' => $this->getRequest()->isSecure()]);
    }
    
    /**
     * getReportUrl
     * @return string
     */
    public function getReportUrl()
    {
        return $this->getUrl('rakutenconnect/product/generatereport', ['_secure' => $this->getRequest()->isSecure()]);
    }
    /**
     * getProfilerUrl
     * @return string
     */
    public function getProfilerUrl()
    {
        return $this->getUrl('rakutenconnect/product/profiler', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getProductDetail
     * @param int $productId which detail want
     * @return false| Magento\Catalog\Model\Product
     */
    public function getProductDetail($productId)
    {
        if ($productId) {
            return $this->productRepository->getById($productId);
        }
        return false;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getMappedProducts()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'ebayconnect.product.list.pager'
            )->setCollection(
                $this->getMappedProducts()
            );
            $this->setChild('pager', $pager);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * getProImgUrl
     * @param Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProImgUrl($products)
    {
        return $this->imageHelper->init($products, 'product_page_image_small')
                                    ->setImageFile($products->getFile())
                                    ->getUrl();
    }

    /**
     * getDeleteProUrl
     * @param int proMapId
     * @return string
     */
    public function getDeleteProMapUrl($proMapId)
    {
        return $this->getUrl(
            'rakutenconnect/product/delete',
            ['id' => $proMapId, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * getDeleteProUrl
     * @param int proMapId
     * @return string
     */
    public function getUpdateStatusUrl()
    {
        return $this->getUrl(
            'rakutenconnect/producttorakuten/updatestatus',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * get report status of product
     *
     * @return boolean
     */
    public function getReportStatus()
    {
        $reportStatus = $this->helper->getSellerRktnCredentials(true)->toArray();
        if (empty($reportStatus['listing_report_id'])) {
            return false;
        } else {
            return $reportStatus['created_at'];
        }
    }

    /**
     * check exported product error
     *
     * @param string $errorMsg
     * @return bool
     */
    public function checkError($errorMsg)
    {
        if (empty($errorMsg)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getProductStatus($status)
    {
        return $this->helper->getAmzProductStatus($status);
    }

    /**
     * check temp count
     *
     * @return bool
     */
    public function getTempCount()
    {
        $sellerId = $this->customerSession->getCustomerId();
        $collection = $this->helper->getTotalImported('product', $sellerId, true);
        return $collection->getSize();
    }
}
