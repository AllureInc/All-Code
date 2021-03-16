<?php

namespace Prince\Faq\Model\Source;

class FaqCategories implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Prince\Faq\Model\FaqGroup
     */
    protected $faqGroupModel;

    /**
     * @param \Prince\Faq\Model\FaqGroup $faqGroup
     */
    public function __construct(\Prince\Faq\Model\FaqGroup $faqGroup)
    {
        $this->faqGroupModel = $faqGroup;
    }

    /**
     * Retrieve options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        print_r($this->faqGroupModel->getCollection()->_toOptionArray('faqgroup_id', 'groupname', 'group_type'));
        // print_r(get_class_methods($this->faqGroupModel->getCollection()));
        die;
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value, 'labeltitlee' => '__'.$value];
        }

        return $result;
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return ['' => __('Select'), 'customer' => __('Customer'), 'vendor' => __('Vendor'), 'both' => __('Both')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}