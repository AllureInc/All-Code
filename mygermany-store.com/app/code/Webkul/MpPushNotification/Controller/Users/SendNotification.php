<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpPushNotification\Controller\Users;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Webkul\MpPushNotification\Api\UsersTokenRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Webkul MpPushNotification user SendNotification controller.
 */
class SendNotification extends Action
{
    const PATH = 'marketplace/mppushnotification/';

    const CHROME = 'Chrome';

    const FIREFOX = 'Firefox';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * UsersTokenRepository
     */
    protected $_usersTokenRepo;

    /**
     * \Webkul\PushNotification\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context                                $context
     * @param Session                                $customerSession
     * @param UsersTokenRepositoryInterface          $usersTokenRepo
     * @param \Webkul\MpPushNotification\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        UsersTokenRepositoryInterface $usersTokenRepo,
        \Magento\Customer\Model\Url $url,
        StoreManagerInterface $storemanager,
        \Webkul\MpPushNotification\Model\Templates $template,
        \Webkul\Marketplace\Helper\Data $helperMarketplace,
        \Webkul\MpPushNotification\Helper\Data $helper
    ) {
        $this->_storeManager = $storemanager;
        $this->template = $template;
        $this->helper = $helperMarketplace;
        $this->url = $url;
        $this->_customerSession = $customerSession;
        $this->_usersTokenRepo = $usersTokenRepo;
        $this->_helper = $helper;
        parent::__construct(
            $context
        );
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
        $loginUrl =  $this->url->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Delete seller products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                $chrome = [];
                $mozila = [];
                $params = $this->getRequest()->getParams();
                $tokens = $this->_usersTokenRepo->getByIds($params['user_mass_delete']);
                $templateData = $this->template->load($params['template']);
                $mediaUrl = $this->_storeManager->getStore()
                            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $logo = $mediaUrl.'marketplace/mppushnotification/'.$templateData->getLogo();
              
                $notificationData = [];
                $notification = [];
                $notificationData['title'] =  $templateData->getTitle();
                $notificationData['body'] = $templateData->getMessage();
                $notificationData['actions'][0]['action'] = $templateData->getUrl();
                $notificationData['actions'][0]['title'] = $templateData->getTitle();
                $notificationData['icon'] = $logo;
                $notification['notification'] = $notificationData;
                foreach ($tokens as $key => $user) {
                    $user->setTemplateId($params['template'])->save();
                    $response = $this->_helper->sendToChrome($user->getToken(), $notification);
                }
                $this->messageManager->addSuccess(
                    __('Push notification(s) has been sent')
                );

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addSuccess(
                    __($e->getMessage())
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
