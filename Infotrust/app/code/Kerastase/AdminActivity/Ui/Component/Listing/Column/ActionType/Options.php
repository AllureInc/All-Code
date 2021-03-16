<?php
/**
 * Kerastase
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customize this module for your needs.
 *
 *
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
 * @copyright  Copyright (C) 2018 Kiwi Commerce Ltd (https://Kerastase.co.uk/)
 * @license    https://Kerastase.co.uk/magento2-extension-license/
 */
namespace Kerastase\AdminActivity\Ui\Component\Listing\Column\ActionType;

/**
 * Class Options
 * @package Kerastase\AdminActivity\Ui\Component\Listing\Column\ActionType
 */
class Options implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Kerastase\AdminActivity\Helper\Data
     */
    public $helper;

    /**
     * Options constructor.
     * @param \Kerastase\AdminActivity\Helper\Data $helper
     */
    public function __construct(\Kerastase\AdminActivity\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * List all option to get in filter
     * @return array
     */
    public function toOptionArray()
    {
        $data = [];
        $lableList = $this->helper->getAllActions();
        foreach ($lableList as $key => $value) {
            $data[] = ['value'=> $key,'label'=> __($value)];
        }
        return $data;
    }
}
