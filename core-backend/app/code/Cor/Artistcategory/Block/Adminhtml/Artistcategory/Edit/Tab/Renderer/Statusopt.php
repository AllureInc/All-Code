<?php
namespace Cor\Artistcategory\Block\Adminhtml\Artistcategory\Edit\Tab\Renderer;
use Magento\Framework\DataObject;
 
class Statusopt extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }
 
    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        if ($row->getStatus() == 0) {
            return __('Disabled');
        } else {
            return __('Enabled');
        }
    }
}
