<?php

namespace Mangoit\NewsletterCustom\Controller\Magento\Newsletter\Adminhtml\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use \DrewM\MailChimp\MailChimp;


class Save extends \Magento\Newsletter\Controller\Adminhtml\Template\Save
{
	/**
     * MailChimp Helper
     *
     * @var \Ebizmarts\MailChimp\Helper\Data
     */
    protected $_mailChimpHelper;

	/**
     * MailChimp API
     *
     * @var \DrewM\MailChimp\MailChimp
     */
    protected $_mailChimpAPI;

	/**
     * Store Manager Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

	/**
     * Mail Template TransportBuilder
     *
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

	/**
     * View Block Factory
     *
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_fileSystemIo;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Ebizmarts\MailChimp\Helper\Data $mailChimpHelper
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
	 * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ebizmarts\MailChimp\Helper\Data $mailChimpHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Filesystem\Io\File $filesystemio
    ) {
        parent::__construct($context);
        $this->_mailChimpHelper = $mailChimpHelper;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder= $transportBuilder;
        $this->_blockFactory = $blockFactory;
        $this->_fileSystemIo = $filesystemio;
    }

	/**
     * Save Newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/template'));
        }
        $template = $this->_objectManager->create('Magento\Newsletter\Model\Template');

        $id = (int)$request->getParam('id');
        if ($id) {
            $template->load($id);
        }

        try {
            $template->addData(
                $request->getParams()
            )->setTemplateSubject(
                $request->getParam('subject')
            )->setTemplateCode(
                $request->getParam('code')
            )->setTemplateSenderEmail(
                $request->getParam('sender_email')
            )->setTemplateSenderName(
                $request->getParam('sender_name')
            )->setTemplateText(
                $request->getParam('text')
            )->setTemplateStyles(
                $request->getParam('styles')
            )->setModifiedAt(
                $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate()
            );

            if (!$template->getId()) {
                $template->setTemplateType(TemplateTypesInterface::TYPE_HTML);
            }
            if ($this->getRequest()->getParam('_change_type_flag')) {
                $template->setTemplateType(TemplateTypesInterface::TYPE_TEXT);
                $template->setTemplateStyles('');
            }
            if ($this->getRequest()->getParam('_save_as_flag')) {
                $template->setId(null);
            }

            $template->save();

            /*
             * Added code for Mailchimp API.
             */

            $apiKey = $this->_mailChimpHelper->getApiKey();
        	$this->_mailChimpAPI = new MailChimp($apiKey);
        	$previewBlock = $this->_blockFactory->createBlock('Mangoit\NewsletterCustom\Block\Adminhtml\Template\Preview');
            $templateHtml = $previewBlock->getTemplateHtmlData($template->getId());

            /*
             * Code to alter the Stylesheets.
             */
            if(preg_match_all('/@import (url\()?"(.*?)"(?(1)\);)/', $templateHtml, $matches)) {

                foreach ($matches[2] as $idx => $cssUrl) {
                    $cssContents = $this->_fileSystemIo->read($cssUrl);
                    $templateHtml = str_replace($matches[0][$idx], $cssContents, $templateHtml);
                }
            }
            /*
             * END
             * Code to alter the Stylesheets.
             */

        	$result = [];
        	if ($id) {
        		$mailChimpTempId = $template->getMailchimpTempId();
        		if($mailChimpTempId) {
	        		$result = $this->_mailChimpAPI->patch("templates/$mailChimpTempId", [
						'name' => $template->getTemplateCode(),
						'html' => $templateHtml,
					]);
        		}
        	} else {
	        	$result = $this->_mailChimpAPI->post("templates", [
					'name' => $template->getTemplateCode(),
					'html' => $templateHtml,
				]);
        	}

        	if (isset($result['id'])) {
        		$template->setMailchimpTempId($result['id'])->save();
        	}

            /*
             * Added code for Mailchimp API. 
             * END
             */

            $this->messageManager->addSuccess(__('The newsletter template has been saved.'));
            $this->_getSession()->setFormData(false);
            $this->_getSession()->unsPreviewData();
            $this->_redirect('*/template');
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_getSession()->setData('newsletter_template_form_data', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving this template.'));
            $this->_getSession()->setData('newsletter_template_form_data', $this->getRequest()->getParams());
        }

        $this->_forward('new');
    }

    public function generateTemplate($templateId)
    {
        $emailTemplateVariables = [
            'firstname' => 'John',
            'lastname' => 'Doe',
        ];

        $template = $this->_transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ])->setTemplateVars($emailTemplateVariables);

        return $template;
    }
	
}
