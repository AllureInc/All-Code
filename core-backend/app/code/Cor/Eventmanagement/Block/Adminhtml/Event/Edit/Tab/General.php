<?php
namespace Cor\Eventmanagement\Block\Adminhtml\Event\Edit\Tab;

class General extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_countryCollectionFactory;
    protected $_systemStore;
    protected $test;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        // \CountryCollectionFactory $countryCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        // $this->_countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $countryArray = [];
        $disabled = 0;
        /* @var $model \Magento\Cms\Model\Page */
        $options = [['label'=> 'No', 'value'=> 0], ['label'=> 'Yes', 'value'=> 1]];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directoryManager = $objectManager->create('Magento\Directory\Block\Data');
        $country = $directoryManager->getCountryCollection();
        $countryHelper = $objectManager->get('Magento\Directory\Model\Config\Source\Country'); 
        $countryFactory = $objectManager->get('Magento\Directory\Model\CountryFactory');
        $countries = $countryHelper->toOptionArray();
        // $countryObjectManager = $objectManager->create('Magento\Directory\Model\Country');
        foreach ( $countries as $countryKey => $country ) {
            if ( $country['value'] != '' ) { //Ignore the first (empty) value
                $stateArray = $countryFactory->create()->setId(
                    $country['value']
                )->getLoadedRegionCollection()->toOptionArray(); //Get all regions for the given ISO country code
                if ( count($stateArray) > 0 ) { //Again ignore empty values
                    $countries[$countryKey]['states'] = $stateArray;
                }
            }
        }
        foreach ($countries as $countryKey => $country)
        {
            if ($country['value'] != '') {
                $countryArray[] = ['label'=> $country['label'], 'value'=> $country['value']];
            }
        }
        $model = $this->_coreRegistry->registry('cor_events');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));
        if ($model->getId()) {
            if ($model->getEventStatus() == 1) {
                $disabled = 1;
            }
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $fieldset->addField(
            'event_start_date',
            'date',
            array(
                'name' => 'event_start_date',
                'label' => __('Start Date'),
                'title' => __('start date'),
                'format' => 'yy-mm-dd',
                'class' => 'form-field',
                'disabled' => $disabled,
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_end_date',
            'date',
            array(
                'name' => 'event_end_date',
                'label' => __('End Date'),
                'title' => __('end date'),
                'format' => 'yy-mm-dd',
                'class' => 'form-field',
                'disabled' => $disabled,
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_name',
            'text',
            array(
                'name' => 'event_name',
                'label' => __('Name'),
                'title' => __('name'),
                'disabled' => $disabled,
                'class' => "validate-alphanum-with-spaces form-field",
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_street',
            'text',
            array(
                'name' => 'event_street',
                'label' => __('Street'),
                'title' => __('street'),
                'class' => 'form-field',
                'disabled' => $disabled,
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_city',
            'text',
            array(
                'name' => 'event_city',
                'class'=> "validate-alphanum-with-spaces form-field",
                'label' => __('City'),
                'title' => __('city'),
                'disabled' => $disabled,
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_state',
            'text',
            array(
                'name' => 'event_state',
                'label' => __('State'),
                'title' => __('state'),
                'class' => "validate-alphanum-with-spaces form-field",
                'disabled' => $disabled,
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_zip',
            'text',
            array(
                'name' => 'event_zip',
                'label' => __('Zip Code'),
                'title' => __('zip'),
                'disabled' => $disabled,
                'class' => 'validate-not-negative-number validate-number validate-no-empty form-field',
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_country',
            'select',
            array(
                'name' => 'event_country',
                'label' => __('Country'),
                'title' => __('country'),
                'class' => 'form-field',
                'values' => $countryArray,
                'value'=> 'US',
                'disabled' => $disabled,
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_capacity',
            'text',
            array(
                'name' => 'event_capacity',
                'label' => __('Capacity'),
                'title' => __('capacity'),
                'disabled' => $disabled,
                'class' => 'validate-not-negative-number validate-number validate-no-empty form-field',
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_phone',
            'text',
            array(
                'name' => 'event_phone',
                'label' => __('Phone'),
                'title' => __('phone'),
                'disabled' => $disabled,
                'class' => 'validate-phoneStrict',
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_time_zone',
            'select',
            array(
                'name' => 'event_time_zone',
                'label' => __('Time Zone'),
                'title' => __('event_time_zone'),
                'class' => 'form-field',
                'disabled' => $disabled,
                'values' => $this->getTimeZoneLocale(),
                'required' => true,
            )
        );
        $fieldset->addField(
            'event_status',
            'select',
            array(
                'name' => 'event_status',
                'label' => __('Closed'),
                'title' => __('event_status'),
                'class' => 'event_status',
                'values' => $options,
                'required' => true,
            )
        );  
        
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }
        $formdata = $model->getData();
        if (!isset($formdata['event_country'])) {
            $formdata['event_country'] = 'US';
        }
        $form->setValues($formdata);
        // $form->setValues($model->getData());
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

    /**
     * Method for retrive Magento's timezones
     *
     * @return array
     */
    public function getTimeZoneLocale()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeValue = $objectManager->get('Magento\Config\Model\Config\Source\Locale\Timezone');
        $locale = $storeValue->toOptionArray();
        return $locale;
    }
}
