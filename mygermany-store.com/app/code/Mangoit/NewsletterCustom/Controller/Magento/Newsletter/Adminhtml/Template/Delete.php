<?php

namespace Mangoit\NewsletterCustom\Controller\Magento\Newsletter\Adminhtml\Template;

use \DrewM\MailChimp\MailChimp;

class Delete extends \Magento\Newsletter\Controller\Adminhtml\Template\Delete
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
        \Magento\Framework\View\Element\BlockFactory $blockFactory
    ) {
        parent::__construct($context);
        $this->_mailChimpHelper = $mailChimpHelper;
        // $this->_storeManager = $storeManager;
        // $this->_transportBuilder= $transportBuilder;
        // $this->_blockFactory = $blockFactory;
    }

    /**
     * Delete newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $template = $this->_objectManager->create(
            'Magento\Newsletter\Model\Template'
        )->load(
            $this->getRequest()->getParam('id')
        );
        if ($template->getId()) {
            try {

                /*
                 * Added code for Mailchimp API.
                 */

                $apiKey = $this->_mailChimpHelper->getApiKey();
                $this->_mailChimpAPI = new MailChimp($apiKey);

                $mailChimpTempId = $template->getMailchimpTempId();
                
                $template->delete();

                if($mailChimpTempId) {
                    $result = $this->_mailChimpAPI->delete("templates/$mailChimpTempId");
                } else {
                    $this->messageManager->addWarning('Template could not be deleted from Mailchimp.');
                }

                /*
                 * Added code for Mailchimp API. 
                 * END
                 */

                $this->messageManager->addSuccess(__('The newsletter template has been deleted.'));
                $this->_getSession()->setFormData(false);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t delete this template right now.'));
            }
        }
        $this->_redirect('*/template');
    }
}
