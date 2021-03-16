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

class ErrorButton extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $errorStatus = $row->getErrorStatus();
        if (!empty($errorStatus)) {
                $htmlButotn = "<button class='action-default scalable save primary' type='button' title='View Error' id='product-grid-view-error' data-msg='$errorStatus'>
                <span class='ui-button-text'>
                    <span>View Error</span>
                </span>
            </button>";
        } else {
            $htmlButotn = 'N/A';
        }
        
        return $htmlButotn;
    }
}
