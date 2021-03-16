<?php
 
namespace Mangoit\VendorPayments\Block\Adminhtml\Invoices\Renderer;
 
use Magento\Framework\DataObject;
 
class RenderRow extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $marketplaceHelper
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
    }
 
    /**
     * get category name
     * 
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $index = $this->getColumn()->getIndex();

        $sellerData = $this->marketplaceHelper->getSellerDataBySellerId($row->getSellerId())->getData();

        if(!empty($sellerData)) {
            $sellerData = $sellerData[0];
            $shopUrl = $this->marketplaceHelper->getRewriteUrl(
                    'marketplace/seller/profile/shop/'.$sellerData['shop_url']
                );

            if($index == 'seller_name') {
                return $sellerData['name'];
            } elseif($index == 'shop_name'){
                return "<a target='_blank' href='".$shopUrl."'>{$sellerData['shop_title']}</a>";
            }
        } else {
            return "NA";
        }
    }
}