<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_TranslationSystem
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Solutions Private Limited
 */

namespace Mangoit\TranslationSystem\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\MailException;
use MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface as ContainerInterface;
/**
 * Mangoit Marketplace Helper Email.
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $_template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_messageManager;

    protected $attachmentFactory;

    /**
     * @param Magento\Framework\App\Helper\Context              $context
     * @param Magento\Framework\ObjectManagerInterface          $objectManager
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param Session                                           $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession,
        \MangoIt\EmailAttachments\Model\AttachmentFactory $attachmentFactory
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->attachmentFactory = $attachmentFactory;
    }


    public function attachContent($content, $pdfFilename, $mimeType, ContainerInterface $attachmentContainer)
    {
        $attachment = $this->attachmentFactory->create(
            [
                'content'  => $content,
                'mimeType' => $mimeType,
                'fileName' => $pdfFilename
            ]
        );
        $attachmentContainer->addAttachment($attachment);
    }

    public function attachPdf($pdfString, $pdfFilename, ContainerInterface $attachmentContainer)
    {
        $this->attachContent($pdfString, $pdfFilename, 'application/pdf', $attachmentContainer);
    }

    public function attachCsv($csvString, $csvFilename, ContainerInterface $attachmentContainer)
    {
        $this->attachContent($csvString, $csvFilename, 'text/csv', $attachmentContainer);
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
}
