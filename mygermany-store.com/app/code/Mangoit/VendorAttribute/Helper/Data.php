<?php

namespace Mangoit\VendorAttribute\Helper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
* 
*/
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $orderId;
    protected $countryCode;
    protected $eavConfig;
    protected $_objectManager;
    protected $attributeFactory;
    protected $attributeSetFactory;
    protected $catalogTransporterFactory;
    protected $eavTypeFactory;
    protected $attributeGroupFactory;
    protected $attributeManagement;
    protected $_eavSetupFactory;
    protected $_storeManager;
    protected $_cattributeFactory;
    protected $_attributeRepository;
    protected $_attributeOptionManagement;
    protected $_option;
    protected $_eavConfig;
    // ---------- email ----------
    protected $transport;
    protected $scopeConfig;
    protected $_inlineTranslation;
    protected $transportBuilder;
    // ---------- email ----------

	function __construct(
        // ----------------------------------- email -------------------------------
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        // ----------------------------------- email -------------------------------
        \Magento\Framework\App\Helper\Context $context, 
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\AttributeFactory  $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory $attributeGroupFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        // \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Eav\Api\AttributeManagementInterface $attributeManagement,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $cattributeFactory,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterface $attributeOptionLabel,
        \Magento\Eav\Model\Entity\Attribute\Option $option,
        \Magento\Framework\ObjectManagerInterface $objectmanager)
	{
		parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;

        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavTypeFactory = $typeFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeManagement = $attributeManagement;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_storeManager = $storeManager;
        $this->_cattributeFactory = $cattributeFactory;
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeOptionManagement = $attributeOptionManagement;
        $this->_option = $option;
        $this->_attributeOptionLabel = $attributeOptionLabel;
        $this->_eavConfig = $eavConfig;
        $this->_objectManager = $objectmanager;
	}

    public function getScopeConfigValue($configPath) // for email
    {
        $scopeValue = $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $scopeValue;
    }

    public function sendEmailToVendor($vendorName, $vendorEmail, $oldAttr, $newAttr, $sellerStoreId) // for email
    {
        $logger = $this->_objectManager->create('Psr\Log\LoggerInterface');
       /* echo "<br> vendor:".$vendorName."<br> email ".$vendorEmail."<br> oldAttr ".$oldAttr."<br> newAttr ".$newAttr;  */
        // die("in helper");
           
            // $salesName = $this->getScopeConfigValue('trans_email/ident_sales/name'); // sender
            // $salesEmail = $this->getScopeConfigValue('trans_email/ident_sales/email'); // sender

            // $toName = $this->getScopeConfigValue('trans_email/ident_general/name'); // receiver
            // $toEmail = $this->getScopeConfigValue('trans_email/ident_general/email');  // receiver

            /* Add sender and receiver details here */
            $toName  = $this->getScopeConfigValue('trans_email/ident_general/name'); // sender
            $toEmail = $this->getScopeConfigValue('trans_email/ident_general/email'); // sender
            $logger->info(" ### sendEmailToVendor : Attribute Mail ###");
            $logger->info(" ### Sender Name: ".$toName." ### ");
            $logger->info(" ### Sender Email: ".$toEmail." ### ");
           /* echo "<br>toName ".$toName;
            echo "<br>toEmail ".$toEmail;*/
             // $toName  = $customerName; // sender
            // $toEmail = $customerEmail; // sender

            $salesName = $vendorName;// receiver
            $salesEmail = $vendorEmail;  // receiver
            $oldAttribute = $oldAttr;
            $newAttribute = $newAttr;

            $logger->info(" ### receiver Name: ".$salesName." ### ");
            $logger->info(" ### receiver Email: ".$salesEmail." ### ");


            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData(
                [
                    'name'=> ($salesName != null) ? $salesName : 'cschmalisch@mygermany.com', 
                    'newattr'=> $newAttribute, 
                    'attribute'=> $oldAttribute
                ]
            );
            // print_r($postObject);
            /*$sender = [
                'name' => $salesName,
                'email' => $salesEmail,
            ];*/

            $sender = [
                'name' => $toName,
                'email' => $toEmail,
            ];

            // $pathToEmalFile1 =  DirectoryList::VAR_DIR.'/ImportEmail/'.$fileName1;
            // $pathToEmalFile2 =  DirectoryList::VAR_DIR.'/ImportEmail/'.$fileName2;

            $email_template = 82;
            if ($sellerStoreId == 7) {
                $email_template = 83;
            } else {
                $email_template = 82;
            }

            $logger->info(" ### sellerStoreId: ".$sellerStoreId." ### ");
            $logger->info(" ### email_template: ".$email_template." ### ");
        
            
            $this->_inlineTranslation->suspend();
            $transport = $this->transportBuilder
            ->setTemplateIdentifier($email_template)
            ->setTemplateOptions(
              [
                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
              ]
            )
            ->setTemplateVars(['data' => $postObject])
            ->setFrom($sender)
            ->addTo(($salesEmail != null) ? $salesEmail : 'cschmalisch@mygermany.com'); // ->addTo($toEmail);
            $transport->getTransport()->sendMessage();
            // print_r($transport);
            
            $this->_inlineTranslation->resume();
            /** Email Code End **/
            $logger->info(" ### sendEmailToVendor : Attribute Mail Sent ### ");
            return true;
    }

    public function getCustomerIdFromSession()
    {
        $sessionModel = $this->_objectManager->create('Magento\Customer\Model\Session');
        $customerId = $sessionModel->getCustomer()->getId();
        return $customerId;
    }

    public function getVendorProductAttribute()
    {
        $attrCollection = $this->_objectManager->create('Mangoit\VendorAttribute\Model\Attributemodel');
        return $attrCollection;
    }

    public function getCustomAttributeOption($attributeCode)
    {
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
       // echo "<pre>";
       // print_r($attribute->getCollection()->getData());
       // // print_r($attribute->getFrontendInput());
       // // print_r($attribute->getFrontendInput());
       // print_r(get_class_methods($attribute));
       // die("121");
        return $attribute;
    }

    public function addAttributeToAllAttributeSets($attributeCode,$attributeGroupCode) {
        // echo "<pre>";
        // print_r(get_class_methods($this->eavTypeFactory->create()));
        // die("<br>41524");
        $entityType = $this->eavTypeFactory->create()->loadByCode('catalog_product');
        // $entityType = $this->eavTypeFactory->create()->load('catalog_product');

        $attribute = $this->attributeFactory->create()->loadByCode($entityType->getId(), $attributeCode);

        if (!$attribute->getId()) {
            return false;
        }

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection */
        $setCollection = $this->attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());

        /** @var Set $attributeSet */
        foreach ($setCollection as $attributeSet) {
            /** @var Group $group */
            $group = $this->attributeGroupFactory->create()->getCollection()
                    ->addFieldToFilter('attribute_group_code', ['eq' => $attributeGroupCode])
                    ->addFieldToFilter('attribute_set_id', ['eq' => $attributeSet->getId()])
                    ->getFirstItem();

            $groupId = $group->getId() ? : $attributeSet->getDefaultGroupId();

            // Assign:
            $this->attributeManagement->assign(
                    'catalog_product', $attributeSet->getId(), $groupId, $attributeCode, $attributeSet->getCollection()->count() * 10
            );
        }

        return true;
    }
}