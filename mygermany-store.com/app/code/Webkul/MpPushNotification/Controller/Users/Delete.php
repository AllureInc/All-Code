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

/**
 * Webkul MpPushNotification user Delete controller.
 */
class Delete extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /*
    UsersTokenRepository
     */
    protected $_usersTokenRepo;
    protected $url;
    protected $helper;
    /**
     * @param Context                       $context
     * @param Session                       $customerSession
     * @param UsersTokenRepositoryInterface $usersTokenRepo
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $helper,
        UsersTokenRepositoryInterface $usersTokenRepo
    ) {
        $this->helper = $helper;
        $this->url = $url;
        $this->_customerSession = $customerSession;
        $this->_usersTokenRepo = $usersTokenRepo;
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
        $loginUrl = $this->url->getLoginUrl();

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
        $helper =  $this->helper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                $params = $this->getRequest()->getParams();
                $tokens = $this->_usersTokenRepo->getByIds($params);
                foreach ($tokens as $user) {
                    $user->delete();
                }
                $this->messageManager->addSuccess(
                    __('Record has been deleted successfully')
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
