<?php
namespace Cor\Artist\Block\Adminhtml\Artist\Edit\Tab;
class General extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $model = $this->_coreRegistry->registry('cor_artist');
        $checkboxValue = $model->getWnineReceived();
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $fieldset->addField(
            'artist_name',
            'text',
            array(
                'name' => 'artist_name',
                'label' => __('Artist Name'),
                'title' => __('Artist Name'),
                'required' => true,
            )
        );
        $fieldset->addField(
            'artist_rep_name',
            'text',
            array(
                'name' => 'artist_rep_name',
                'label' => __('Artist Rep Name'),
                'title' => __('artist rep name'),
                'required' => true,
            )
        );
        $fieldset->addField(
            'artist_rep_number',
            'text',
            array(
                'name' => 'artist_rep_number',
                'label' => __('Artist Rep Number'),
                'title' => __('artist rep number'),
                'class' => "validate-phoneStrict",
                'required' => true,
            )
        );
        $fieldset->addField(
            'artist_rep_email',
            'text',
            array(
                'name' => 'artist_rep_email',
                'label' => __('Artist Rep Email'),
                'title' => __('artist rep email'),
                'class' => "email",
                'required' => true,
            )
        );
        $fieldset->addField(
            'artist_tax_id',
            'text',
            array(
                'name' => 'artist_tax_id',
                'label' => __('Tax Id'),
                'title' => __('tax id'),
            )
        );
        $fieldset->addField(
            'wnine_received',
            'checkbox',
            array(
                'name' => 'wnine_received',
                'label' => __('W9 Received'),
                'class' => 'wnine',
                'title' => __('w9 received'),
                'data-form-part' => $this->getData('target_form'),
                'onchange' => 'this.value = this.checked;',
                'checked' => isset($checkboxValue) ? $checkboxValue : 0,
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
