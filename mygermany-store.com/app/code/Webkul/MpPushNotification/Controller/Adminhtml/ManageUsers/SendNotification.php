<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Controller\Adminhtml\ManageUsers;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpPushNotification\Controller\Adminhtml\ManageUsers;
use Webkul\MpPushNotification\Model\ResourceModel\UsersToken\CollectionFactory;
use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;
use Webkul\MpPushNotification\Model\UsersToken;
use Magento\Store\Model\StoreManagerInterface;

class SendNotification extends ManageUsers
{
    const PATH = 'mppushnotification/';

    const CHROME = 'Chrome';

    const FIREFOX = 'Firefox';

    /**
     * object of store manger class
     * @var storemanager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * TemplatesRepositoryInterface
     */
    protected $_templatesRepository;

    /**
     * filter object of Filter
     * @var Filter
     */
    protected $_filter;

    /**
     * UsersToken
     */
    protected $_usersToken;

    /**
     * \Webkul\PushNotification\Helper\Data
     */
    protected $_helper;

    /**
     * [__construct description]
     * @param Context                              $context
     * @param Filter                               $filter
     * @param PageFactory                          $resultPageFactory
     * @param CollectionFactory                    $collectionFactory
     * @param TemplatesRepositoryInterface         $templatesRepository
     * @param StoreManagerInterface                $storemanager
     * @param \Webkul\MpPushNotification\Helper\Data $helper
     * @param UsersToken                           $usersToken
     */
    public function __construct(
        Context $context,
        Filter $filter,
        PageFactory $resultPageFactory,
        CollectionFactory $collectionFactory,
        TemplatesRepositoryInterface $templatesRepository,
        StoreManagerInterface $storemanager,
        \Webkul\MpPushNotification\Model\Templates $template,
        \Webkul\MpPushNotification\Helper\Data $helper,
        UsersToken $usersToken
    ) {
        $this->template = $template;
        $this->_filter = $filter;
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_templatesRepository = $templatesRepository;
        $this->_storeManager = $storemanager;
        $this->_helper = $helper;
        $this->_usersToken = $usersToken;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        $templateId = $this->getRequest()->getParam('entity_id');
        $model = $this->_filter;
        $collection = $model->getCollection($this->_collectionFactory->create());

        $response = $this->notificationData($templateId, $collection);

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * manage notification data
     * @param  int $templateId
     * @param  object $collection
     * @return int|null
     */
    protected function notificationData($templateId, $collection)
    {
        $chrome = [];
        $mozila = [];
        $baseUrl = $this->_storeManager->getStore()
        ->getBaseUrl();
        $domainUrl =  parse_url($baseUrl, PHP_URL_HOST);
        $templateData = $this->template->load($templateId);
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $logo = $mediaUrl.'marketplace/mppushnotification/'.$templateData->getLogo();
      
        $notificationData = [];
        $notification = [];
        $notificationData['title'] =  $templateData->getTitle();
        $notificationData['body'] = $templateData->getMessage();
        $notificationData['actions'][0]['action'] = $templateData->getUrl();
        $notificationData['actions'][0]['title'] = $templateData->getTitle();
        $notificationData['icon'] = $logo;
        $notification['notification'] = $notificationData;
        foreach ($collection as $key => $user) {
            $user->setTemplateId($templateId)->save();
            $response = $this->_helper->sendToChrome($user->getToken(), $notification);
        }
        $this->messageManager->addSuccess(
            __('Push notification(s) has been sent')
        );
    }
}
