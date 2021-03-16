<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Block\Adminhtml\Accounts\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class AmazonProduct extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Mangoit\RakutenConnector\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $amzProStatus = $row->getRakutenProId();
        if (empty($amzProStatus)) {
            $amzProStatus = 'N/A';
        }
        return $amzProStatus;
    }
}
