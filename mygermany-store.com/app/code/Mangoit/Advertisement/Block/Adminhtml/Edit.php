<?php 
namespace Mangoit\Advertisement\Block\Adminhtml;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
* 
*/
class Edit extends \Magento\Backend\Block\Template
{
	protected $_objectManagaer;
	// protected $_template = 'Mangoit_Advertisement::newadvertisement.phtml';
    protected $_storeRepository;
    protected $_session;
    protected $_storeManager;

	public function __construct(
        \Magento\Store\Model\StoreRepository $storeRepository,
		\Magento\Framework\ObjectManagerInterface $objectmanager,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $session,
		\Magento\Backend\Block\Template\Context $context		
		)
	{
		$this->_session = $session;
        $this->_storeRepository = $storeRepository;
		$this->_objectManagaer = $objectmanager;
		$this->_storeManager = $storeManager;
		parent::__construct($context);
	}

	public function getContentTypeOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getContentType();
	}

	public function getProductIdOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getProductId();
	}

	public function getCategoryIdOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getCategoryId();
	}

	public function getStoreIdOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getStoreId();
	}

	public function getUrlOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getUrl();
	}

	public function getTitleOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getTitle();
	}

	public function getStatusOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getEnable();
	}

	public function getImageOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		$storeId = $adminAdModel->getStoreId();
		if ($storeId == 'All Store') {
			$storeId = 0;
		}
		$blockId = $adminAdModel->getWebkulBlockId();
		$imageName = $adminAdModel->getImageName();
		 $mediaUrl = $this->_storeManager
                     ->getStore()
                     ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $path = $mediaUrl.'webkul/MpAdvertisementManager/0/'.$storeId.'/'.$blockId.'/'.$imageName;
		// $path = $this->_objectManagaer->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('webkul/MpAdvertisementManager/0/'.$storeId.'/'.$blockId.'/'.$imageName);
		return $path;
	}

	public function getHtmlOfAd($id)
	{
		$adminAdModel = $this->_objectManagaer->create('Mangoit\Advertisement\Model\Adsadmin');
		$adminAdModel->load($id);
		return $adminAdModel->getImageName();
	}

	public function getAllStoreList()
    {
        $stores = $this->_storeRepository->getList();
        return $stores;
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
}