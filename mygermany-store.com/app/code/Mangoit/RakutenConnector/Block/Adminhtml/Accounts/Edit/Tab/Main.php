<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Block\Adminhtml\Accounts\Edit\Tab;

use \Mangoit\RakutenConnector\Model\Config\Source;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $countryList;


    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Directory\Model\Config\Source\Country $countryList
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSet
     * @param Source\AllStoreList $allStoreList
     * @param Source\AllWebsiteList $allWebsiteList
     * @param Source\CategoriesList $categoriesList
     * @param Source\ImportType $importType
     * @param \Magento\Sales\Model\Config\Source\Order\Status $orderStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\Config\Source\Country $countryList,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSet,
        Source\AllStoreList $allStoreList,
        Source\AllWebsiteList $allWebsiteList,
        Source\CategoriesList $categoriesList,
        Source\MarketplaceSellers $sellers,
        Source\ImportType $importType,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Mangoit\RakutenConnector\Model\Config\Source\AmazonMarketplace $amzMarketplace,
        array $data = []
    ) {
        $this->countryList = $countryList;
        $this->attributeSet = $attributeSet;
        $this->storeManager = $context->getStoreManager();
        $this->allStoreList = $allStoreList;
        $this->allWebsiteList = $allWebsiteList;
        $this->categoriesList = $categoriesList;
        $this->importType = $importType;
        $this->orderStatus = $orderStatus;
        $this->amzMarketplace = $amzMarketplace;
        $this->sellers = $sellers;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getAttributeSets()
    {
        $attributSetArray = [];
        $attributeSet =  $this->attributeSet->toOptionArray();
        foreach ($attributeSet as $key => $value) {
            $attributSetArray[$value['value']] = $value['label'];
        }
        return $attributSetArray;
    }


    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('amazon_user');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Rakuten Account Information')]);

        if ($model->getEntityId()) {
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        if ($model->getEntityId()) {
            $baseFieldset->addField(
                'seller_id',
                'select',
                [
                    'name' => 'seller_id',
                    'label' => __('Seller'),
                    'id' => 'seller_id',
                    'title' => __('Seller'),
                    'values' => $this->sellers->toArray(),
                    'disabled' => true,

                ]
            );
        } else {
            $baseFieldset->addField(
                'seller_id',
                'select',
                [
                    'name' => 'seller_id',
                    'label' => __('Seller'),
                    'id' => 'seller_id',
                    'title' => __('Seller'),
                    'values' => $this->sellers->toArray(),
                    'class' => 'required-entry'
                ]
            );
        }

        $baseFieldset->addField(
            'attribute_set',
            'select',
            [
                'name' => 'attribute_set',
                'label' => __('Attribute Set'),
                'id' => 'attribute_set',
                'title' => __('Attribute Set'),
                'values' => $this->getAttributeSets(),
                'class' => 'required-entry'
            ]
        );
        /*$baseFieldset->addField(
            'marketplace_id',
            'select',
            [
                'name' => 'marketplace_id',
                'label' => __('Marketplace'),
                'id' => 'marketplace_id',
                'title' => __('Rakuten Marketplace'),
                'values' => $this->amzMarketplace->toArray(),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'amz_seller_id',
            'text',
            [
                'name' => 'amz_seller_id',
                'label' => __('Rakuten Seller Id'),
                'id' => 'amz_seller_id',
                'title' => __('Rakuten Seller Id'),
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'access_key_id',
            'password',
            [
                'name' => 'access_key_id',
                'label' => __('Access Key Id'),
                'id' => 'access_key_id',
                'title' => __('Rakuten Access Key Id'),
                'class' => 'required-entry',
                'required' => true
            ]
        );*/
        $baseFieldset->addField(
            'secret_key',
            'password',
            [
                'name' => 'secret_key',
                'label' => __('Secret Key'),
                'id' => 'secret_key',
                'title' => __('Rakuten Secret Key'),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'revise_item',
            'select',
            [
                'label' => __('Revise Rakuten Product'),
                'title' => __('Revise Rakuten Product'),
                'required' => true,
                'index' => 'revise_item',
                'name' => 'revise_item',
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );
        $baseFieldset->addField(
            'default_cate',
            'select',
            [
                'name' => 'default_cate',
                'label' => __('Default Category'),
                'id' => 'default_cate',
                'title' => __('Default Category'),
                'values' => $this->categoriesList->toOptionArray(),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'default_store_view',
            'select',
            [
                'name' => 'default_store_view',
                'label' => __('Default Store View'),
                'id' => 'default_store_view',
                'title' => __('Default Store View'),
                'values' => $this->allStoreList->toOptionArray(),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'product_create',
            'select',
            [
                'name' => 'product_create',
                'label' => __('Product Create'),
                'id' => 'product_create',
                'title' => __('Product Create'),
                'values' => $this->importType->toOptionArray(),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'default_website',
            'select',
            [
                'name' => 'default_website',
                'label' => __('Default Website'),
                'id' => 'default_website',
                'title' => __('Default Website'),
                'values' => $this->allWebsiteList->toOptionArray(),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'order_status',
            'select',
            [
                'name' => 'order_status',
                'label' => __('Order Status'),
                'id' => 'order_status',
                'title' => __('Order Status'),
                'values' => $this->orderStatus->toOptionArray(),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'default_pro_qty',
            'text',
            [
                'name' => 'default_pro_qty',
                'label' => __('Default Product Qty'),
                'id' => 'default_pro_qty',
                'title' => __('Default Product Qty'),
                'class' => 'required-entry validate-number validate-greater-than-zero',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'default_pro_weight',
            'text',
            [
                'name' => 'default_pro_weight',
                'label' => __('Default Product Weight'),
                'id' => 'default_pro_weight',
                'title' => __('Default Product Weight'),
                'class' => 'required-entry validate-number validate-greater-than-zero',
                'required' => true
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
