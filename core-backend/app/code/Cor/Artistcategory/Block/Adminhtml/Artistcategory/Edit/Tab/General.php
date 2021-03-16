<?php
namespace Cor\Artistcategory\Block\Adminhtml\Artistcategory\Edit\Tab;
class General extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $objectManager;
    protected $_urlInterface;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $id = 0;
        /* @var $model \Magento\Cms\Model\Page */
        $options = [['label'=> 'Disabled', 'value'=> 0], ['label'=> 'Enabled', 'value'=> 1]];
        $url = $this->getUrl('artistcategory/category/checkcategory');
        $status  = 1;
        $model = $this->_coreRegistry->registry('cor_artistcategory_registry');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));

        if ($model->getId()) {
            $id = $model->getId();
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        
        $fieldset->addField(
            'posturl',
            'hidden',
            array(
                'name' => 'posturl',
                'class' => 'posturl'
            )
        );
        $fieldset->addField(
            'postid',
            'hidden',
            array(
                'name' => 'postid',
                'class' => 'postid'
            )
        );
        $fieldset->addField(
            'category_name',
            'text',
            array(
                'name' => 'category_name',
                'label' => __('Category Name'),
                'title' => __('category name'),
                'class' => 'validate-alphanum-with-spaces',
                'min' => '3',
                'required' => true,
            )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('status'),
                'values' => $options,
                'value' => $status ? $status : 0,
                /*'required' => true,*/
            )
        );
        
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }
        $formData = $model->getData();
        $formData['posturl'] = $url;
        $formData['postid'] = $id;
        $form->setValues($formData);
        $this->setForm($form);

        return parent::_prepareForm();   
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General');
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
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
