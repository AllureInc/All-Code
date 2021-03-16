<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
//@codingStandardsIgnoreFile
namespace Webkul\MpPushNotification\Controller\Adminhtml\Templates;

use Webkul\MpPushNotification\Controller\Adminhtml\Templates;
use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Save extends Templates
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * TemplatesRepositoryInterface
     */
    protected $_templatesRepository;

    /**
     * \Webkul\MpPushNotification\Model\Templates
     */
    protected $_templatesModel;

    /**
     * [__construct description]
     * @param \Magento\Backend\App\Action\Context         $context
     * @param \Magento\Framework\View\Result\PageFactory  $resultPageFactory
     * @param TemplatesRepositoryInterface                $templatesRepository
     * @param \Webkul\MpPushNotification\Model\Templates    $templatesModel
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Registry                 $coreRegistry
     * @param TimezoneInterface                           $localeDate
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        TemplatesRepositoryInterface $templatesRepository,
        \Webkul\MpPushNotification\Model\Templates $templatesModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Registry $coreRegistry,
        TimezoneInterface $localeDate
    ) {
        parent::__construct($context);
        $this->_date = $date;
        $this->resultPageFactory = $resultPageFactory;
        $this->_templatesRepository = $templatesRepository;
        $this->_templatesModel = $templatesModel;
        $this->_coreRegistry = $coreRegistry;
        $this->localeDate = $localeDate;
    }

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $returnToEdit = false;
        $requestData = $this->getRequest()->getPostValue();
        $templateId = isset($requestData['pushnotification_template']['id'])
            ? $requestData['pushnotification_template']['id']
            : null;
        if ($requestData) {
            try {
                
                $templateData = $requestData['pushnotification_template'];
                $templateData['logo'] = $this->getLogoName($templateData);
                $templateData['logo_url'] = $requestData['pushnotification_template']['logo'][0]['url'];
                if (!isset($templateData['seller_id']) || !$templateData['seller_id']) {
                    $templateData['seller_id'] = '0';
                }
                $request = $this->getRequest();
                $isExistingTempate = (bool) $templateId;
                $templateModel = $this->_templatesModel;
                if ($isExistingTempate) {
                    $currentTemplate = $this->_templatesRepository->getById($templateId);
                    $templateData['id'] = $templateId;
                }
                $templateData['updated_at'] =  $this->localeDate->date()->format('Y-m-d H:i:s');
               
                if (!$isExistingTempate) {
                    $templateData['created_at'] =  $this->localeDate->date()->format('Y-m-d H:i:s');
                }
             
                $templateModel->setData($templateData);
                // Save template
                if ($isExistingTempate) {
                    $this->templateModel->save();
                } else {
                    $templates = $templateModel->save();
                    $templateId = $templates->getId();
                }
                $this->_getSession()->unsTemplateFormData();
                // Done Saving template, finish save action
                $this->_coreRegistry->register('template_id', $templateId);
                $this->messageManager->addSuccess(__('You saved the template.'));
                $returnToEdit = (bool) $this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setTemplateFormData($requestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __('Something went wrong while saving the template. %1', $exception->getMessage())
                );
                $this->_getSession()->setTemplateFormData($requestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($templateId) {
                $resultRedirect->setPath(
                    'mppushnotification/templates/edit',
                    ['id' => $templateId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'mppushnotification/templates/createnew',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('mppushnotification/templates/index');
        }

        return $resultRedirect;
    }

    private function getLogoName($templateData)
    {
        if (isset($templateData['logo'][0]['name'])) {
            if (isset($templateData['logo'][0]['name'])) {
                return $templateData['logo'] = $templateData['logo'][0]['name'];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please upload logo.')
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please upload logo.')
            );
        }
    }
}
