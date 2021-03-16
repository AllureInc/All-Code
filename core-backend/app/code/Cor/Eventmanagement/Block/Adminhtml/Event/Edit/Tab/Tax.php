<?php
namespace Cor\Eventmanagement\Block\Adminhtml\Event\Edit\Tab;
class Tax extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

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
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('cor_events');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Tax')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $fieldset->addField(
            'tax_values',
            'text',
            array(
                'name' => 'tax_values',
                'label' => __('Tax Values'),
                'title' => __('tax values'),
                /*'required' => true,*/
            )
        );

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();   
    }

    /**
     * Method for retrive event status
     *
     * @return boolean
     */
    public function getEventStatus($id)
    {
       $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
       $eventModel = $objectManager->create('Cor\Eventmanagement\Model\Event');
       $eventModel->load($id);
       return $eventModel->getEventStatus();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Tax');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Tax');
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

    /**
     * Method for retrive categories collection
     *
     * @return collection[]
     */
    public function getCategoriesData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('Cor\Artistcategory\Model\Artistcategory');
        $collection = $model->getCollection()->addFieldToFilter('status',['eq' => 1]);
        return $collection;
    }

    /**
     * Method for retrive event data from database
     *
     * @return model (object)
     */
    public function getEventsData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('Cor\Eventmanagement\Model\Event');
        return $model;
    }
}
