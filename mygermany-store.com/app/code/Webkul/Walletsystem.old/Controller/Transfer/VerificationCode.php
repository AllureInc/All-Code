<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class VerificationCode extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = [];
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            __('Verification Code')
        );
        $getEncodedParamData = $this->getRequest()->getParam('parameter');
        $paramsJson = base64_decode(urldecode($getEncodedParamData));
        if ($paramsJson) {
            $params = json_decode($paramsJson, true);
        }
        if (is_array($params) && count($params) && array_key_exists('sender_id', $params)) {
            return $resultPage;
        } else {
            $this->messageManager->addError(__("Please try again later."));
            return $this->resultRedirectFactory->create()->setPath(
                'walletsystem/transfer/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
