<?php
namespace Mangoit\Sellerapi\Controller\Seller;

class Generatetoken extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_block;
	protected $_messageManager;
	protected $resultRedirectFactory;
	protected $_logger;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Mangoit\Sellerapi\Block\Seller\View $block,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\Controller\Result\Redirect $resultRedirect,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\View\Result\PageFactory $pageFactory
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->_block = $block;
		$this->_messageManager = $messageManager;
		$this->resultRedirectFactory = $resultRedirect;
		$this->_logger = $logger;
		parent::__construct($context);
	}

	public function execute()
	{
		$resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
		/*return $this->_pageFactory->create();*/
		$params = $this->getRequest()->getParams();
		$isSeller = $this->_block->getLoggedInCustomer();
		if ($isSeller['is_seller']) {
			$seller_id = $isSeller['seller_id'];
			$token = $this->_block->getAuthorisedTokenKey($isSeller['customer_id']);
			$this->_logger->info('### _block->getAuthorisedTokenKey rsult: '.json_encode($token));

			if (isset($token['error']) && $token['error'] == true) {
				$this->_messageManager->addError(__(''.$token['msg']));
				$this->_logger->info('### _messageManager->addError '.$token['msg']);
				return $resultRedirect;
				$this->_logger->info('### return with error ###');
			}

			$this->_messageManager->addSuccess(__('Authorized token has been created.'));
			/*echo $token;
			exit();*/
			/*echo "<pre>";
			echo "Token: ";
			print_r($token);
			die("..");*/
			return $resultRedirect;
		} else {
			$this->_messageManager->addError(__('Please login first'));
			/*echo "<pre>";
			echo "Else Token: ";
			print_r($token);
			die("..00");*/
			/*echo false;
			exit();*/
			return $resultRedirect;
		}

	}
}