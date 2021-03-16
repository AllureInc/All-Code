<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Controller\Adminhtml\Templates;

use Webkul\MpPushNotification\Controller\Adminhtml\Templates;
use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
 
class Edit extends Templates
{

    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $_resultPageFactory;

    /*
    TemplatesRepositoryInterface
     */
    protected $_templatesRepository;

    /**
     * [__construct description]
     * @param Filesystem                                 $filesystem
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param TemplatesRepositoryInterface               $templatesRepository
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Filesystem $filesystem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        TemplatesRepositoryInterface $templatesRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_templatesRepository = $templatesRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->_filesystem = $filesystem;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    /**
     * Template edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $baseUrl = $this->_storeManager->getStore()
        ->getBaseUrl();
        $domainUrl =  parse_url($baseUrl, PHP_URL_HOST);
        $templateId = $this->initCurrentTemplate();
        $isExistingTemplate = (bool)$templateId;
        if ($isExistingTemplate) {
            try {
                $logoContainerDir = $this->_filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('marketplace/mppushnotification/');
                $logoUrl = $this->_storeManager->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).'marketplace/mppushnotification/';
                if (!is_dir($logoContainerDir)) {
                    mkdir($logoContainerDir, 0755, true);
                }
                $baseUrl = $this->_storeManager->getStore()
                ->getBaseUrl();
                $baseTmpPath = 'marketplace/mppushnotification';

                $templateData = [];
                $templateData['pushnotification_template'] = [];
                $templateObject = null;
                $templateObject = $this->_templatesRepository->getById($templateId);
                $result = $templateObject->getData();
                if (!empty($result)) {
                    $templateData['pushnotification_template'] = $result;
                    $templateData['pushnotification_template']['logo'] = [];
                    $templateData['pushnotification_template']['logo'][0] = [];
                    $templateData['pushnotification_template']['logo'][0]['name'] = $result['logo'];
                    $img = explode($domainUrl, $result['logo_url']);
                    if (isset($img[1])) {
                        $templateData['pushnotification_template']['logo'][0]['url'] =
                        $logoUrl.$result['logo'];
                    } else {
                        $templateData['pushnotification_template']['logo'][0]['url'] =
                        'https://'.$domainUrl.$result['logo_url'];
                    }
                    $filePath = $this->_mediaDirectory->getAbsolutePath(
                        $baseTmpPath
                    ).$result['logo'];
                    if (file_exists($filePath)) {
                        $templateData['pushnotification_template']['logo'][0]['size'] =
                        filesize($filePath);
                    } else {
                        $templateData['pushnotification_template']['logo'][0]['size'] = 0;
                    }
                    $templateData['pushnotification_template']['entity_id'] = $templateId;

                    $this->_getSession()->setTemplateFormData($templateData);
                } else {
                    $this->messageManager->addError(
                        __('Requested template doesn\'t exist')
                    );
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('mppushnotification/templates/index');
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while editing the template.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('mppushnotification/templates/index');
                return $resultRedirect;
            }
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpPushNotification::templates');
        $this->prepareDefaultTemplateTitle($resultPage);
        $resultPage->setActiveMenu('Webkul_MpPushNotification::templates');
        if ($isExistingTemplate) {
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Template with id %1', $templateId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Template'));
        }
        return $resultPage;
    }

    /**
     * tempalate initialization
     *
     * @return string template id
     */
    protected function initCurrentTemplate()
    {
        $templateId = (int)$this->getRequest()->getParam('id');

        if ($templateId) {
            $this->_coreRegistry->register('template_id', $templateId);
        }

        return $templateId;
    }

    /**
     * Prepare banner default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultTemplateTitle(
        \Magento\Backend\Model\View\Result\Page $resultPage
    ) {
        $resultPage->getConfig()->getTitle()->prepend(__('Template Information'));
    }
}
