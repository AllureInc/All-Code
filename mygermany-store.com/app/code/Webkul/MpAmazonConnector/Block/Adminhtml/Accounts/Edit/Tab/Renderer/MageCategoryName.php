<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Adminhtml\Accounts\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class MageCategoryName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $product = $this->productRepository->getById($row->getMagentoProId());
        $categoryIds = $product->getCategoryIds();
        $categories = $this->categoryFactory->create()->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('entity_id',array('in'=>$categoryIds));
        $names = "";
        foreach ($categories as $category) {
            $names .= $category->getName() . '<br><br>';
        }
        return $names;
    }
}
