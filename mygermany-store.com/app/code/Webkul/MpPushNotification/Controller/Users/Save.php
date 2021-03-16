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
use Magento\Framework\App\Action\Context;
use Webkul\MpPushNotification\Model\UsersToken;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MpPushNotification\Api\UsersTokenRepositoryInterface;

class Save extends Action
{

    const NAME = 'guest';
    /**
     * @var UsersToken
     */
    protected $_userTokenModel;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var UsersTokenRepository
     */
    protected $_usersTokenRepository;

    /**
     * \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * [__construct description]
     * @param Context                                     $context
     * @param UsersToken                                  $userTokenModel
     * @param JsonFactory                                 $resultJsonFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param UsersTokenRepositoryInterface               $usersTokenRepository
     */
    public function __construct(
        Context $context,
        UsersToken $userTokenModel,
        JsonFactory $resultJsonFactory,
        UsersTokenRepositoryInterface $usersTokenRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
        $this->_userTokenModel = $userTokenModel;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_usersTokenRepository = $usersTokenRepository;
        $this->_customerSession = $customerSession;
    }
  
    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        
        $result = null;
        $time = $this->_timezoneInterface->date()->format('m/d/y H:i:s');
        $params = $this->getRequest()->getParams();
        $tokenCollection = $this->_usersTokenRepository->getByToken($params['token']);
        if ($this->_customerSession->isLoggedIn()) {
            $params['name'] = $this->_customerSession->getCustomer()->getName();
        } else {
            $params['name'] = self::NAME;
        }
        if (!$tokenCollection->getSize()) {
            $params['created_at'] = $time;
            $params['token'] = $params['token'];
            $id = $this->_userTokenModel
                ->addData($params)->save()
                ->getId();
            if ($id) {
                $result = ['error' => false, 'info' => $id];
            } else {
                $result = ['error'=> 'user token did not saved in database' ];
            }
        }
        return $this->_resultJsonFactory->create()->setData($result);
    }
}
