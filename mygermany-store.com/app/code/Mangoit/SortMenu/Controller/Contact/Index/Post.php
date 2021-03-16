<?php
namespace Mangoit\SortMenu\Controller\Contact\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Mangoit\SortMenu\Helper\Data;
use Webkul\Marketplace\Helper\Data as WebkulData;
use Mangoit\Marketplace\Helper\Corehelper;

class Post extends \Magento\Contact\Controller\Index\Post
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var MailInterface
     */
    protected $mail;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var LoggerInterface
     */
    protected $_mailHelper;

    /**
     * @var LoggerInterface
     */
    protected $_webkuldata;

    /**
     * @var LoggerInterface
     */
    protected $_corehelper;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger = null,
        Data $mailHelper = null,
        WebkulData $webkuldata = null,
        Corehelper $corehelper = null
    ) {
        parent::__construct($context, $contactsConfig, $mail, $dataPersistor, $logger);
        $this->context = $context;
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->_mailHelper = $mailHelper ?: ObjectManager::getInstance()->get(Data::class);
        $this->_webkuldata = $webkuldata ?: ObjectManager::getInstance()->get(WebkulData::class);
        $this->_corehelper = $corehelper ?: ObjectManager::getInstance()->get(Corehelper::class);
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $adminStoremail = $this->_webkuldata->getAdminEmailId();
            $adminEmail = $adminStoremail? $adminStoremail:$this->_webkuldata->getDefaultTransEmailId();

            $this->sendEmail($this->validatedParams());

            $senderInfo = [
                'name' => $this->_corehelper->adminEmailName(),
                'email' => $adminEmail,
            ];

            $receiverInfo = [
                'name' => $this->getRequest()->getParam('name'),
                'email' => $this->getRequest()->getParam('email')
            ];

            $emailTemplateVariables['name'] = $this->getRequest()->getParam('name');

            $this->_mailHelper->sendResponseEmail($emailTemplateVariables,$senderInfo,$receiverInfo);
            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('contact_us');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    /**
     * @param array $post Post data from contact form
     * @return void
     */
    protected function sendEmail($post)
    {
        $this->mail->send(
            $post['email'],
            ['data' => new DataObject($post)]
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function validatedParams()
    {
        $request = $this->getRequest();
        if (trim($request->getParam('name')) === '') {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }

        return $request->getParams();
    }
}
