<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category Ced
 * @package Ced_Productfaq
 * @author CedCommerce Core Team
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Productfaq\Controller\Adminhtml\Productfaq;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{

    const XML_PATH_EMAIL_SENDER_NAME = 'faq/general/sender_name';
    const XML_PATH_EMAIL_SENDER_EMAIL = 'faq/general/sender_email';
    protected $_transportBuilder;
    
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
    ) {
    
        $this->date = $date;
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_escaper = $escaper;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $description=$this->getRequest()->getParam('description');
        $email=$this->getRequest()->getParam('email_id');
        $date = $this->date->gmtDate();
        //var_dump($data);
        /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect 
*/
        $resultRedirect = $this->resultRedirectFactory->create();
        $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setData($data);
                $model->setUpdatedAt($this->date->gmtDate());

                try {
                    $model->save();
                    if($data['email_id']) {
                        $this->sendTransactional($email, $description);
                    }
                    $this->messageManager->addSuccess(__('The FAQ has been saved.'));
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                    }
                    return $resultRedirect->setPath('*/*/');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving the faq.'));
                }

                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
            else{
            
                $productids= $data['product_id'];
                foreach ($productids as $key=>$val)
                {
                    $data['product_id']=$val;
                    $data['posted_by'] = 'myGermany Gmbh';
                    $data['publish_date']=$date;
                    $data['updated_at']= $date;
                    $model->setData($data);
                
                    try {
                        $model->save();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\Exception $e) {
                        $this->messageManager->addException($e, __('Something went wrong while saving the faq.'));
                    }
                }
                $this->messageManager->addSuccess(__('The FAQ has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
    /*
     *Sending Email Notification to customer 
     **/
    public function sendTransactional($email,$description)
    {
    	if(!$this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER_EMAIL))
    	{
    		return;
    	}
        $this->inlineTranslation->suspend();
        try {
            $error = false;
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $sender = [
            'name' => $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER_NAME),
            'email' =>$this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER_EMAIL),
            ];
          
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier('send_faq_email_template') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                    [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['answer' => $description])
                ->setFrom($sender)
                ->addTo($email)
                ->getTransport();
        
            $transport->sendMessage(); ;
            $this->inlineTranslation->resume();
            return;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            $this->_redirect('*/*/');
            return;
        }
    }
}
