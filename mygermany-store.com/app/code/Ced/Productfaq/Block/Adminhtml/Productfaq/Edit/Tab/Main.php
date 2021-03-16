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
namespace Ced\Productfaq\Block\Adminhtml\Productfaq\Edit\Tab;

/**
 * FAQ  edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Magento\Store\Model\System\Store       $systemStore
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Ced\Productfaq\Model\Status $status,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_status = $status;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    protected function _construct()
    {
    
        parent::_construct();
        $this->setId('post_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('FAQS'));
    }
    
    public function getValues()
    {
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', '1')
            ->addAttributeToFilter('visibility', '4');
        foreach($collection as $key => $value){
            $arr[$value->getId()] = $value->getName();
        }
        foreach ($arr as $key=>$val){
            $options[]= array('value'=>$key, 'label'=>$val);
        }
        return $options;
    }
    /**
     * Prepare form
     *
     * @return form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('ced_faq');
        $isElementDisabled = false;

        /**
 * @var \Magento\Framework\Data\Form $form 
*/
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Frequently Asked Questions')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);

            $fieldset->addField(
                'product_id',
                'text',
                [
                'name' => 'product_id',
                'label' => __('Product Id'),
                'title' => __('Product Id'),
                'required' => true,
                'disabled' => $isElementDisabled
                ]
            );
        }
        else 
         {
            $fieldset->addField(
                'product_id',
                'multiselect',
                [
                'label' => __('Product Name'),
                'title' => __('Product Name'),
                'required' => true,
                'name' => 'product_id[]',
                'disabled' => $isElementDisabled,
                'values'=>$this->getValues()
                ]
            );
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title/Question'),
                'title' => __('Title/Question'),
                'required' => true,
                'disabled' => $isElementDisabled
                ]
        );

        //   $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);

        $contentField = $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Answer'),
                'title' => __('Answer'),
                'required' => true,
                'disabled' => $isElementDisabled,
              //  'config' => $wysiwygConfig
            ]
        );

        // Setting custom renderer for content field to remove label column
        /*      $renderer = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element'
        )->setTemplate(
            'Magento_Cms::page/edit/form/renderer/content.phtml'
        );
        $contentField->setRenderer($renderer);
        */

        $fieldset->addField(
            'email_id',
            'text',
            [
                'name' => 'email_id',
                'label' => __('Email'),
                'title' => __('Email'),
                'disabled' => $isElementDisabled
                ]
        );
        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => $this->_status->getOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('FAQ TAB');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
