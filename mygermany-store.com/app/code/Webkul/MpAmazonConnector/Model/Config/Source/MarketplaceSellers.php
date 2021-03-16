<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model\Config\Source;

class MarketplaceSellers
{
    private $mpSeller;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Webkul\Marketplace\Model\SellerFactory $mpSellerFactory,
        \Webkul\MpAmazonConnector\Model\AccountsFactory $accountsFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->mpSellerFactory = $mpSellerFactory;
        $this->accountsFactory = $accountsFactory;
        $this->request = $request;
    }


    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray()
    {
        $sellers = $this->setJoinOntable();
        $alreadyMapped = $this->accountsFactory->create()
                        ->getCollection()
                        ->getColumnValues('seller_id');
        
        if (!$this->request->getParam('id')) {
            $sellerArray = in_array(0, $alreadyMapped) ? [] : ['0' => __('Admin')];
            foreach ($sellers as $seller) {
                if (!in_array($seller->getSellerId(), $alreadyMapped)) {
                    $sellerArray[$seller->getSellerId()] = $seller->getName();
                }
            }
        } else {
            $sellerArray = ['0' => __('Admin')];
            foreach ($sellers as $seller) {
                $sellerArray[$seller->getSellerId()] = $seller->getName();
            }
        }
        
        return $sellerArray;
    }

    /**
     * set join to get desire result
     *
     * @return void
     */
    private function setJoinOntable()
    {
        $sellers = $this->mpSellerFactory->create()->getCollection()
                ->addFieldToFilter('store_id', 0);
        $customerEntity = $sellers->getTable('customer_grid_flat');

        $sellers->getSelect()->join(
            $customerEntity.' as cpev',
            'main_table.seller_id = cpev.entity_id',
            ['name']
        );
        return $sellers;
    }

    /**
     * get sellers
     *
     * @return array
     */
    public function getSellers()
    {
        $sellers = $this->setJoinOntable();
        $sellerArray[] = ['value'=>'0', 'label' => __('Admin')];
        foreach ($sellers as $seller) {
            $sellerArray[] = ['value' =>$seller->getSellerId(), 'label' =>$seller->getName()];
        }
        return $sellerArray;
    }
}
