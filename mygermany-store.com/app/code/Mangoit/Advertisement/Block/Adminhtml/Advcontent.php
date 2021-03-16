<?php 
namespace Mangoit\Advertisement\Block\Adminhtml;

/**
* 
*/
class Advcontent extends \Magento\Backend\Block\Template
{
	protected $_objectManagaer;
	protected $_template = 'Mangoit_Advertisement::advertisement.phtml';
     protected $_storeRepository;
    protected $_session;
    protected $scopeConfig;
    protected $date;

	public function __construct(
        \Magento\Store\Model\StoreRepository $storeRepository,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Backend\Block\Template\Context $context
		)
	{
        $this->_session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->_storeRepository = $storeRepository;
        $this->date = $date;
		$this->_objectManagaer = $objectmanager;
		parent::__construct($context);
	}
	
	public function getBlockObjectManager()
	{
		return $this->_objectManagaer;
	}	

    public function getAllStoreList()
    {
        $stores = $this->_storeRepository->getList();
        return $stores;
    }

    public function getStoreConfigData()
    {
        return $this->scopeConfig->getValue('marketplace/ads_settings/ads_config_settings', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    // public function getCategoriesTree()
    // {
    //     $catData = $this->_objectManagaer->create('Webkul\Marketplace\Block\Product\Create');
    //     echo "<pre>";
    //     print_r(get_class_methods($catData));
    //     print_r(get_class_methods($catData->getPersistentData()));
    //     die("<br>00");
    // }

	/*protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::COMM_TEMPLATE);
        }

        return $this;
    }*/

    public function isThereAnyActiveAd($blockPostionOfThisblock, $blockValidity)
    {
        $adPurchase = $this->_objectManagaer->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetail');
        $adPurchaseCollection = $adPurchase->getCollection()->addFieldToFilter('block_position', ['eq'=> $blockPostionOfThisblock]);
         // $adPurchaseCollection = $adPurchase->getCollection()->addFieldToFilter('block_position', ['eq'=> 25]);
         if ($adPurchaseCollection->getData()) {
            $date = date_create($this->date->gmtDate());
            // echo "<br> current : ".$date;
            foreach ($adPurchaseCollection->getData() as $item) {
                $time = date_create($item['created_at']);
                $diff = date_diff($time, $date);
                $dateDiff = intval($diff->format("%a "));
                $blockValidity = intval($blockValidity);
                if ($dateDiff <= $blockValidity) {
                    return true;
                }
            }
             
         } 
         
        return false;
    }

    public function getCustomerId()
    {
        return $this->_session->getCustomer()->getId();
    }
    protected function _prepareLayout()
    {

        return parent::_prepareLayout();
    }

    public function getCategories()
    {
    	$categoryCollection = $this->_objectManagaer->create('Magento\Catalog\Model\Category');
    	return $categoryCollection->getCollection();
    }

    public function getProductList()
    {
    	$productCollection = $this->_objectManagaer->create('Magento\Catalog\Model\Product');
    	return $productCollection->getCollection();
    }

    public function getAdBlockData($id)
    {
        $model = $this->_objectManagaer->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $block = $model->load($id);
        return $block;
    }

    public function getAdBlockId($id)
    {
        $model = $this->_objectManagaer->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $block = $model->load($id);
        return $block->getBlockPosition();
    }
}