<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\Productfaq\Block;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Productfaq extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $seller;
    protected $productFaq;
    protected $_productloader;  
    protected $_storeManager;    
    protected $_objectManager;    

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        \Webkul\Marketplace\Model\Seller $seller,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->seller = $seller;
        $this->productFaq = $productFaq;
        $this->_productloader = $_productloader;
        $this->_storeManager = $storeManager;        
        $this->_objectManager = $objectmanager;
        parent::__construct($context, $data);
    }


    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getVendorFaqs()
    {   
        $customerSessObj = $this->customerSession->getCustomer();
        $customerId = $customerSessObj->getId();
         //get values of current page
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $paramData = $this->getRequest()->getParams();
        $pname = '';
        $pstatus = '';
        $ptitle = '';

        //MIS changes
        if (isset($paramData['pname'])) {
            $pname = $paramData['pname'] != '' ? $paramData['pname'] : '';
        }
        if (isset($paramData['ptitle'])) {
            $ptitle = $paramData['ptitle'] != '' ? $paramData['ptitle'] : '';
        }

        if (isset($paramData['pstatus'])) {
            $pstatus = $paramData['pstatus'] != '' ? $paramData['pstatus'] : '';
        }
        $catalogProductEntityVarchar = $this->_objectManager->create(
                'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
            )->getTable('catalog_product_entity_varchar');
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;
        $storeId = $this->_storeManager->getStore()->getId();
        $stores = [$storeId, 0];
        $faqCollection = $this->productFaq->getCollection()
        ->addFieldToFilter('vendor_id', $customerId)
        ->addFieldToFilter('main_table.store_id',['eq' => $storeId]);

        $faqCollection->setOrder('title','ASC');
        $faqCollection->setPageSize($pageSize);
        $faqCollection->setCurPage($page);
        $faqCollection->getSelect()->group('product_id');
        $eavAttribute = $this->_objectManager->get(
                'Magento\Eav\Model\ResourceModel\Entity\Attribute'
            );
        //name Column in collection
        $proAttId = $eavAttribute->getIdByCode('catalog_product', 'name');

        $faqCollection->getSelect()->join(
            $catalogProductEntityVarchar.' as product_entity_varchar',
            'main_table.product_id = product_entity_varchar.entity_id AND product_entity_varchar.attribute_id='.$proAttId,
            ['product_name' => 'value']
        );
        if ($ptitle != '') {
            $faqCollection->addFieldToFilter(
                array('title'),
                array(
                    array('like'=>'%'.$ptitle.'%')
                )
            );
        }
        if ($pstatus != '') {
            $pstatus = (($pstatus == 2) ? 0 : 1);
            $faqCollection->addFieldToFilter(
                array('is_active'),
                array(
                    array('like'=>'%'.$pstatus.'%')
                )
            );
        }
        if ($pname != '') {
            $faqCollection->addFieldToFilter(
                array('product_entity_varchar.value'),
                array(
                    array('like'=>'%'.$pname.'%')
                )
            );
        }
        return $faqCollection;
    }
    // public function getNews()
    // {
    //   //get values of current page
    //     $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
    // //get values of current limit
    //     $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 1;


    //     $newsCollection = $this->newscollectionFactory->create();
    //     $newsCollection->addFieldToFilter('is_active',1);
    //     $newsCollection->setOrder('title','ASC');
    //     $newsCollection->setPageSize($pageSize);
    //     $newsCollection->setCurPage($page);
    //     return $newsCollection;
    // }
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        //$this->pageConfig->getTitle()->set(__('News'));


        if ($this->getVendorFaqs()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'fme.news.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getVendorFaqs()
            );
            $this->setChild('pager', $pager);
            $this->getVendorFaqs()->load();
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
}
