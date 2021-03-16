<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Product;

class Profiler extends \Magento\Framework\View\Element\Template
{
    /**
     * Webkul\MpAmazonConnector\Model\ResourceModel\Productmap\CollectionFactory
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
        \Webkul\MpAmazonConnector\Api\AmazonTempDataRepositoryInterface $tempRepository,
        array $data = []
    ) {
        $this->tempRepository = $tempRepository;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * get temp collection of seller
     *
     * @return int
     */
    public function getImportedProduct()
    {
        $tempCollection = $this->tempRepository
                    ->getByAccountIdnItemType(
                        'product',
                        $this->customerSession->getCustomerId(),
                        true
                    );
        return $tempCollection->getSize();
    }

    /**
     * getImportUrl
     * @return string
     */
    public function getCreateProductUrl()
    {
        return $this->getUrl('mpamazonconnect/product/createproduct', ['_secure' => $this->getRequest()->isSecure()]);
    }
}
