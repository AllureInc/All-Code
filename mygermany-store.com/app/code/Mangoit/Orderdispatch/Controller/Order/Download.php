<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\Orderdispatch\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Webkul Marketplace Product Add Controller.
 */
class Download extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    protected $_helper;

    protected $_dhlRetoure;

    protected $_messageManager;
    protected $_packagingSlip;
    protected $_downloader;
    protected $_urlInterface;
    protected $_logger;

    /**
     * @param Context                                       $context
     * @param Webkul\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory    $resultPageFactory
     * @param \Magento\Customer\Model\Session               $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Mangoit\Dhlshipment\Helper\DhlRetoure $dhlRetoure,
        \Mangoit\Dhlshipment\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Mangoit\Orderdispatch\Helper\PackagingSlip $packagingSlip,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_dhlRetoure = $dhlRetoure;
        $this->_messageManager = $messageManager;
        $this->_packagingSlip = $packagingSlip;
        $this->_downloader =  $fileFactory;
        $this->_urlInterface = $urlInterface;
        $this->_logger = $this->getLogger();
        parent::__construct($context);
    }

    public function execute()
    {   
        try {
            $postData = $this->getRequest()->getParams();
            $file = $_SERVER['DOCUMENT_ROOT'].'/pub/media/packageSlip/'.$postData['method'].'/'.$postData['file_name'];
            if (file_exists($file)) {
                return $this->_downloader->create($postData['file_name'], file_get_contents($this->_urlInterface->getBaseUrl().'pub/media/packageSlip/'.$postData['method'].'/'.$postData['file_name']),  DirectoryList::MEDIA, 'application/pdf');
            } else {
                $this->messageManager->addErrorMessage(__('Packaging Slip is not available for this order. Please check your email.')); 
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/order/view',
                    ['id' => $postData['order_id']],                    
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Packaging Slip is not available for this order. Please check your email.')); 
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/order/view',
                ['_secure' => $this->getRequest()->isSecure()]
            );
            $this->_logger->info('Exception : '.$e->getMessage());
        }
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/download_package_slip.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }

}