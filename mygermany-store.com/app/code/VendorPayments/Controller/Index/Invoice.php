<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */
namespace Mangoit\VendorPayments\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class Invoice extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $_url;
    protected $_session;
    protected $marketplaceHelper;


    /**
     * @var Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Mangoit\VendorPayments\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_url = $url;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
