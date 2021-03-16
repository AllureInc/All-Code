<?php 
namespace Mangoit\Advertisement\Block\Adminhtml;

/**
* 
*/
class Newad extends \Magento\Backend\Block\Template
{
	protected $_objectManagaer;
	// protected $_template = 'Mangoit_Advertisement::newadvertisement.phtml';
     protected $_storeRepository;
    protected $_session;

	public function __construct(
        \Magento\Store\Model\StoreRepository $storeRepository,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Session $session,
		\Magento\Backend\Block\Template\Context $context
		)
	{
		// die('in block');
        $this->_session = $session;
        $this->_storeRepository = $storeRepository;
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

    public function getBlockName()
    {
        $blockData = [];
        $webkulHelper = $this->_objectManagaer->create('Webkul\MpAdvertisementManager\Helper\Data');
        $blockModel = $this->_objectManagaer->create('Webkul\MpAdvertisementManager\Model\Pricing');
        $filteredBlockModel = $blockModel->getCollection();
        foreach ($filteredBlockModel as $item) {
            if ($item['ad_type'] == 1) {
                if ($item['block_position'] == 3) {
                    array_push($blockData,  array('position' => $item['block_position'], 'blockname' => 'Home Seller Ads Page Bottom Container', 'content' => $item['content_type'] ));
                } else if ($item['block_position'] == 2) {
                    array_push($blockData,  array('position' => $item['block_position'], 'blockname' => 'Home Seller Popup Ads', 'content' => $item['content_type']));
                } else if ($item['block_position'] == 1) {
                    array_push($blockData,  array('position' => $item['block_position'], 'blockname' => 'Home Seller Ads Page Top', 'content' => $item['content_type']));
                } else {
                    $blockName = $webkulHelper->getBlockPositionLabel($item['block_position']);
                    array_push($blockData,  array('position' => $item['block_position'], 'blockname' => $blockName, 'content' => $item['content_type']));         
                }
            }
            # code...
        }
        return $blockData;
    }
}