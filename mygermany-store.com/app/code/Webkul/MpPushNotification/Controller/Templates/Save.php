<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Controller\Templates;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Data;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;
    protected $helperMarketplace;
    /**
     * @param Context                                $context
     * @param \Magento\Customer\Model\Url            $url
     * @param \Magento\Customer\Model\Session        $session
     * @param \Webkul\MpPushNotification\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $helperMarketplace,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpPushNotification\Helper\Data $helper
    ) {
        $this->helper = $helperMarketplace;
        $this->_url = $url;
        $this->_session = $session;
        $this->_helper = $helper;
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
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $data['title'] = strip_tags($data['title']);
        $data['message'] = strip_tags($data['message']);
        $data['tags'] = strip_tags($data['tags']);
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $formValidate = $this->_helper->validateFormField($data);

            if (!$formValidate) {
                $this->messageManager->addWarning('No field(s) should be empty.');
                if (array_key_exists('entity_id', $data)) {
                    return $this->resultRedirectFactory->create()
                        ->setPath('*/*/edit', ['id'=>$data['entity_id']]);
                } else {
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }
            }

            if (array_key_exists('entity_id', $data)) {
                $flag = 1;
            } else {
                $flag = 0;
            }
            $imageStatus = $this->_helper->uploadImage();
            if (!$imageStatus) {
                if (!$flag) {
                    $this->messageManager->addError('There was some problem in uploading image.');
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }
            }
            $data['logo'] = $imageStatus;
            $result = $this->_helper->saveTemplate($data);
            if (!$result['errorFlag']) {
                if ($flag) {
                    $this->messageManager->addSuccess(__('Template is updated successfully.'));
                } else {
                    $this->messageManager->addSuccess(__('Template is saved successfully.'));
                }
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            } else {
                $this->messageManager->addError(__('Something Went Wrong.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
