<?php
namespace Mangoit\NewsletterCustom\Cron;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use \DrewM\MailChimp\MailChimp;


class UpdateTemplates
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_objectManager;

    /**
     * @param \Ebizmarts\MailChimp\Helper\Data $mailChimpHelper
	 * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
	 * @param \Magento\Framework\Filesystem\Io\File $filesystemio
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        \Ebizmarts\MailChimp\Helper\Data $mailChimpHelper,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Filesystem\Io\File $filesystemio,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        // parent::__construct($context);
        $this->_mailChimpHelper = $mailChimpHelper;
        $this->_blockFactory = $blockFactory;
        $this->_fileSystemIo = $filesystemio;
        $this->_logger = $logger;
        $this->_logger->pushHandler(new \Monolog\Handler\StreamHandler(BP . '/var/log/mailchimp_templates_log.log'));
        $this->_objectManager = $objectmanager;
    }
 
    public function execute()
    {
    	$this->_logger->info('Started :: Posting Templates on MailChimp API.');
    	try {
            /*
             * Added code for Mailchimp API.
             */
            $templatesCollection = $this->_objectManager->create('Magento\Newsletter\Model\Template')->getCollection();
            $templatesCollection->addFieldToFilter('mailchimp_temp_id', array('neq' => ''));
            
            $apiKey = $this->_mailChimpHelper->getApiKey();
            $this->_mailChimpAPI = new MailChimp($apiKey);
            $previewBlock = $this->_blockFactory->createBlock('Mangoit\NewsletterCustom\Block\Adminhtml\Template\Preview');

            $result = [];
            foreach ($templatesCollection as $template) {
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

                $mailChimpTempId = $template->getMailchimpTempId();
                $result[$mailChimpTempId] = '';
                if($mailChimpTempId) {
                    $result[$mailChimpTempId] = $this->_mailChimpAPI->patch("templates/$mailChimpTempId", [
                        'name' => $template->getTemplateCode(),
                        'html' => $templateHtml,
                    ]);
                }
                $this->_logger->info(json_encode($result));
            }

            $processedTemplatesCount = count($result);
    		$this->_logger->info("Finished :: Processed and Posted $processedTemplatesCount Templates on MailChimp API.");
            /*
             * Added code for Mailchimp API. 
             * END
             */
            return $this;
        } catch (LocalizedException $e) {
        	$this->_logger->error($e->getMessage());
            // $this->messageManager->addError(nl2br($e->getMessage()));
        } catch (\Exception $e) {
        	$this->_logger->error($e->getMessage());
            // $this->messageManager->addException($e, __('Something went wrong while saving this template.'));
        }
    }
}