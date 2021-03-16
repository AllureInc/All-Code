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
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MpPushNotification\Api\UsersTokenRepositoryInterface;
use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class TemplateData extends Action
{
    const PATH = 'marketplace/mppushnotification/';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var UsersTokenRepository
     */
    protected $_usersTokenRepository;

    /*
    TemplatesRepositoryInterface
     */
    protected $_templatesRepo;

    /**
     * object of store manger class
     * @var storemanager
     */
    protected $_storeManager;

    /**
     * [__construct description]
     * @param Context                                    $context
     * @param JsonFactory                                $resultJsonFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param UsersTokenRepositoryInterface              $usersTokenRepository
     * @param TemplatesRepositoryInterface               $templatesRepo
     * @param StoreManagerInterface                      $storemanager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        UsersTokenRepositoryInterface $usersTokenRepository,
        TemplatesRepositoryInterface $templatesRepo,
        StoreManagerInterface $storemanager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_usersTokenRepository = $usersTokenRepository;
        $this->_templatesRepo = $templatesRepo;
        $this->_storeManager = $storemanager;
    }

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $responseData = [];
        $templateId = null;
        $token = $this->getRequest()->getParam('token');

        $tokenCollection = $this->_usersTokenRepository->getByToken($token);

        foreach ($tokenCollection as $user) {
            $templateId = $user->getTemplateId();
        }
        if (!empty($templateId)) {
            $templateData = $this->_templatesRepo->getById($templateId);
            $mediaPath = $this->_storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            if ($templateData->getEntityId()) {
                $responseData['title'] = $templateData->getTitle();
                $responseData['message'] = $templateData->getMessage();
                $responseData['url'] = $templateData->getUrl();
                $responseData['logo'] = $mediaPath.self::PATH.$templateData->getLogo();
                $responseData['tags'] = $templateData->getTags();
            }
        }
        return $this->resultJsonFactory->create()->setData($responseData);
    }
}
