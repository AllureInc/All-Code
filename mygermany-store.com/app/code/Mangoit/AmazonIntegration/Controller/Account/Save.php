<?php
namespace Mangoit\AmazonIntegration\Controller\Account;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Webkul\AmazonMagentoConnect\Model\AccountsFactory;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    private $resultJsonFactory;

    /**
     * @var \Webkul\AmazonMagentoConnect\Helper\Data
     */
    private $dataHelper;

    private $accountsFactory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AccountsFactory $accountsFactory,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        array $data = []
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountsFactory = $accountsFactory;
        $this->dataHelper =  $helper;
        parent::__construct($context, $data);
    }
    /**
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $data = $this->getRequest()->getParams();
        $amzClient = $this->dataHelper->validateAmzCredentials($data);
        if (!$data) {
            $this->_redirect('amazon/account/integration');
            return;
        }
        if ($amzClient) {
            $sellerParticipation = $amzClient->ListMarketplaceParticipations();
            $participateMp = $sellerParticipation['ListMarketplaces']['Marketplace'];
            $participateMp = isset($participateMp[0]) ? $participateMp : [0 => $participateMp];
            foreach ($participateMp as $marketplace) {
                if ($marketplace['MarketplaceId'] === $data['marketplace_id']) {
                    $data['currency_code'] =  $marketplace['DefaultCurrencyCode'];
                    $data['country'] = $marketplace['DefaultCountryCode'];
                }
            }
            
            $model = $this->accountsFactory->create()->load($id);

            if ($id && $model->isObjectNew()) {
                $this->messageManager->addError(__('This account no longer exists.'));
                $this->_redirect('amazon/account/integration');
                return;
            }

            try {
                $amzCollection = $this->accountsFactory->create()->getCollection();
                $amzCollection->addFieldToFilter('store_name', $data['store_name']);
                if ($amzCollection->getSize() && !$id) {
                    $this->messageManager->addError(__('Store Name Already Taken'));
                    $this->_redirect('amazon/account/integration');
                    return;
                }
                if (isset($data['created_at'])) {
                    unset($data['created_at']);
                }

                $id = $model->setData($data)->save()->getId();
                $this->messageManager->addSuccess(__('You saved the amazon seller account detail.'));
            } catch (\Exception $e) {
                $this->messageManager->addMessages(__('something went wrong'));
                $this->_redirect('amazon/account/integration');
            }
            $this->_redirect('amazon/account/integration');
        } else {
            $this->messageManager->addError(__('Amazon account details are not correct'));
            $this->_redirect('amazon/account/integration');
        }
        /** @var \Magento\Framework\View\Result\Page resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}