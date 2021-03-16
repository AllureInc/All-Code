<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the Mega Menu Options
 */
namespace Cnnb\GtmWeb\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Cnnb\GtmWeb\Helper\Data as GtmHelper;
use Magento\Framework\View\Element\Template\Context;

/**
 * StepOptionColumn | Admin Block Class
 */
class StepOptionColumn extends Select
{
    /**
     * @var GtmHelper
     */
    protected $_gtmHelper;

    public function __construct(
        Context $context,
        GtmHelper $gtmHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_gtmHelper = $gtmHelper;
    }
    
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Provides steps
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $stepData = [];
        $diagnosis_attribute_json = $this->_gtmHelper->getDiagnosisData();
        $stepData[] = ['label'=> __('Please Select'),'value'=> ''];
        if (!empty($diagnosis_attribute_json) && isset($diagnosis_attribute_json['steps_data'])) {
            foreach ($diagnosis_attribute_json['steps_data'] as $key => $value) {
                $label = $value['gtm_step'];
                $stepData[] = [
                'label'=> $label,
                'value'=> $label
                ];
           }    
        }
        foreach($stepData as $k => $v) 
        {
            foreach($stepData as $key => $value) 
            {
                if($k != $key && $v['label'] == $value['label'])
                {
                    unset($stepData[$k]);
                }
            }
        }
        
        return $stepData;
    }
}
