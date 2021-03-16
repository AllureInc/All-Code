<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Block\Adminhtml;

/**
 * Class Selector
 * @package Kerastase\AdminActivity\Block\Adminhtml
 */
class Selector extends \Magento\Backend\Block\Template
{
    /**
     * Revert Activity Log action URL
     * @return string
     */
    public function getRevertUrl()
    {
        return $this->getUrl('adminactivity/activity/revert');
    }
}
