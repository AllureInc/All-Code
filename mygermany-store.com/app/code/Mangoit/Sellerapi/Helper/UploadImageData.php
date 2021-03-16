<?php
namespace Mangoit\Sellerapi\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class UploadImageData extends \Magento\Framework\App\Helper\AbstractHelper 
{
	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var SaveProduct
     */
    protected $_saveProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_productResourceModel;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Framework\ObjectManagerInterface 
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface 
     */
    protected $_mangoitBlock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param SaveProduct      $saveProduct
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Mangoit\Sellerapi\Helper\SaveProduct $saveProduct,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Mangoit\Sellerapi\Block\Seller\View $mangoitBlock
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_saveProduct = $saveProduct;
        $this->_productResourceModel = $productResourceModel;
        $this->_objectManager = $objectmanager;
        $this->_mangoitBlock = $mangoitBlock;
        $this->messageManager = $messageManager;
        parent::__construct(
            $context
        );
    }

    public function downloadProductImages($product_imgs, $product_id)
    {

    }
}