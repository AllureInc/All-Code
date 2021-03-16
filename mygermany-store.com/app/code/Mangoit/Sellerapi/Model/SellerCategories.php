<?php 
namespace Mangoit\Sellerapi\Model;

use Mangoit\Sellerapi\Api\SellerCategoriesInterface; 
use Magento\Framework\Webapi\Exception;

class SellerCategories implements SellerCategoriesInterface {

	protected $resultJsonFactory;
	protected $_block;
	protected $_webkulHelper;
	protected $_storeManager;
	protected $_session;
	protected $_customer;
	protected $_sellerProduct;

	public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Mangoit\Sellerapi\Block\Seller\View $block,
		\Webkul\Marketplace\Helper\Data $webkulHelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Customer $customer,
		\Mangoit\Sellerapi\Model\SellerProduct $sellerProduct,
		\Magento\Customer\Model\Session $session
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_block = $block;
        $this->_webkulHelper = $webkulHelper;
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_customer = $customer;
        $this->_sellerProduct = $sellerProduct;
    }

	/**
	 * {@inheritdoc}
	 */
	public function getData($seller_id)
	{
		$token = $this->_sellerProduct->getBearerToken();
		$customer = $this->_customer->load($seller_id);
		if ($customer->getSellerApiToken() && ($customer->getSellerApiToken() == $token)) {
			$categories = $this->_block->getAllowedCategoryIds($seller_id);
			return $categories;
			# code...
		} else {
			throw new Exception(__('Please regenerate your authentication token. Your current authentication token has been expired.'));
		}
	}
}