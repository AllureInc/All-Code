<?php

namespace Mangoit\Marketplace\Block;

use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\Seller;
use Magento\Customer\Model\Customer;

class Marketplace extends \Webkul\Marketplace\Block\Marketplace
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    protected $seller;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $_productRepository;

    /**
     * @param Context                                    $context
     * @param array                                      $data
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Order $order,
        Customer $customer,
        Seller $seller,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        /*$this->Product = $product;
        $this->Customer = $customer;
        $this->_productRepository = $productRepository;
        $this->Seller = $seller;
        $this->Order = $order;
        $this->_filterProvider = $filterProvider;
        $this->_objectManager = $objectManager;*/
        // $this->imageHelper = $context->getImageHelper();
        // die('constant');
        parent::__construct($filterProvider, $objectManager, $product, $productRepository, $order, $customer, $seller, $context, $data);
    }

    public function getBestSaleSellers()
    {
        die('..........');
        $marketplaceUserdata = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('marketplace_userdata');
        $catalogProductEntityInt = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('catalog_product_entity_int');
        $marketplaceProduct = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('marketplace_product');
        $eavAttribute = $this->_objectManager->get(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute'
        );
        $proAttId = $eavAttribute->getIdByCode('catalog_product', 'visibility');

        $helper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $sellersOrder = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )
        ->getCollection()
        ->addFieldToSelect('seller_id');
        $prefix = '';

        if (count($helper->getAllStores()) == 1 && count($helper->getAllWebsites()) == 1) {
            $storeId = 0;
        } else {
            $storeId = $helper->getCurrentStoreId();
        }

        $sellersOrder->getSelect()
        ->join(
            ['ccp' => $marketplaceUserdata],
            'ccp.seller_id = main_table.seller_id',
            ['is_seller' => 'is_seller']
        )->where(
            'ccp.is_seller = 1'
        );

        $sellersOrder->getSelect()
                        ->columns('COUNT(*) as countOrder')
                        ->group('seller_id');
        

        $websiteId = $helper->getWebsiteId();
        $joinTable = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('customer_grid_flat');
        if ($helper->getCustomerSharePerWebsite()) {
            $sellersOrder->getSelect()->join(
                $joinTable.' as cgf',
                'main_table.seller_id = cgf.entity_id AND website_id= '.$websiteId
            );
        } else {
            $sellersOrder->getSelect()->join(
                $joinTable.' as cgf',
                'main_table.seller_id = cgf.entity_id'
            );
        }

        

        $sellerArr = [];
        $sellerIdsArr = [];
        $sellerCountArr = [];
        foreach ($sellersOrder as $value) {
            if ($sellerHelperProCount = $helper->getSellerProCount($value['seller_id'])) {
                $sellerArr[$value['seller_id']] = [];

                array_push($sellerIdsArr, $value['seller_id']);

                $sellerCountArr[$value['seller_id']] = [];
                array_push($sellerCountArr[$value['seller_id']], $sellerHelperProCount);

                $sellerProducts = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleslist'
                )->getCollection()
                ->addFieldToSelect('mageproduct_id')
                ->addFieldToSelect('magequantity')
                ->addFieldToSelect('seller_id')
                ->addFieldToSelect('cpprostatus');
                $sellerProducts->getSelect()
                ->join(
                    ['mpro' => $marketplaceProduct],
                    'mpro.mageproduct_id = main_table.mageproduct_id',
                    ['status' => 'status']
                )->where(
                    'main_table.seller_id='.$value['seller_id'].' 
                    AND main_table.cpprostatus=1 
                    AND mpro.status = 1'
                );
                $sellerProducts->getSelect()
                ->columns('SUM(magequantity) as countOrderedProduct')
                ->group('mageproduct_id');
                $sellerProducts->setOrder('countOrderedProduct', 'DESC');

                $sellerProducts->getSelect()
                ->join(
                    ['cpei' => $catalogProductEntityInt],
                    'cpei.entity_id = main_table.mageproduct_id',
                    ['value' => 'value']
                )->where(
                    'cpei.value=4 
                    AND cpei.attribute_id = '.$proAttId.' 
                    AND cpei.store_id = '.$storeId
                );

                $sellerProducts->getSelect()->limit(3);
                foreach ($sellerProducts as $sellerProduct) {
                    array_push(
                        $sellerArr[$value['seller_id']],
                        $sellerProduct['mageproduct_id']
                    );
                }
                if ((count($sellerProducts) < 3) && $storeId != 0) {
                    $sellerProducts = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleslist'
                    )->getCollection()
                    ->addFieldToSelect('mageproduct_id')
                    ->addFieldToSelect('magequantity')
                    ->addFieldToSelect('seller_id')
                    ->addFieldToSelect('cpprostatus');
                    $sellerProducts->getSelect()
                    ->join(
                        ['mpro' => $marketplaceProduct],
                        'mpro.mageproduct_id = main_table.mageproduct_id',
                        ['status' => 'status']
                    );
                    if (count($sellerArr[$value['seller_id']])) {
                        $sellerProducts->getSelect()->where(
                            'main_table.seller_id='.$value['seller_id'].'
                            AND main_table.mageproduct_id NOT IN ('.implode(',', $sellerArr[$value['seller_id']]).')
                            AND main_table.cpprostatus=1
                            AND mpro.status = 1'
                        );
                    } else {
                        $sellerProducts->getSelect()->where(
                            'main_table.seller_id='.$value['seller_id'].'
                            AND main_table.cpprostatus=1
                            AND mpro.status = 1'
                        );
                    }
                    $sellerProducts->getSelect()
                    ->columns('SUM(magequantity) as countOrderedProduct')
                    ->group('mageproduct_id');
                    $sellerProducts->setOrder('countOrderedProduct', 'DESC');
    
                    $sellerProducts->getSelect()
                    ->join(
                        ['cpei' => $catalogProductEntityInt],
                        'cpei.entity_id = main_table.mageproduct_id',
                        ['value' => 'value']
                    )->where(
                        'cpei.value=4 
                        AND cpei.attribute_id = '.$proAttId.' 
                        AND cpei.store_id = 0'
                    );
                    $remaingCount = 3 - count($sellerArr[$value['seller_id']]);
                    $sellerProducts->getSelect()->limit($remaingCount);
                    foreach ($sellerProducts as $sellerProduct) {
                        array_push(
                            $sellerArr[$value['seller_id']],
                            $sellerProduct['mageproduct_id']
                        );
                    }
                }

                if (count($sellerArr[$value['seller_id']]) < 3) {
                    $sellerProCount = count($sellerArr[$value['seller_id']]);
                    $sellerProductColl = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Product'
                    )->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq' => $value['seller_id']]
                    )
                    ->addFieldToFilter(
                        'mageproduct_id',
                        ['nin' => $sellerArr[$value['seller_id']]]
                    )
                    ->addFieldToFilter(
                        'status',
                        ['eq' => 1]
                    );
                    $sellerProductColl->getSelect()
                    ->join(
                        ['cpei' => $catalogProductEntityInt],
                        'cpei.entity_id = main_table.mageproduct_id',
                        ['value' => 'value']
                    )->where(
                        'cpei.value=4 AND 
                        cpei.attribute_id = '.$proAttId.' AND 
                        cpei.store_id = '.$storeId
                    );
                    $sellerProductColl->getSelect()->limit(3);
                    foreach ($sellerProductColl as $value) {
                        if ($sellerProCount < 3) {
                            array_push(
                                $sellerArr[$value['seller_id']],
                                $value['mageproduct_id']
                            );
                            ++$sellerProCount;
                        }
                    }
                    if ((count($sellerProductColl) < 3) && $storeId != 0) {
                        $sellerProductColl = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Product'
                        )->getCollection()
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $value['seller_id']]
                        )
                        ->addFieldToFilter(
                            'status',
                            ['eq' => 1]
                        );
                        $sellerProductColl->getSelect()
                        ->join(
                            ['cpei' => $catalogProductEntityInt],
                            'cpei.entity_id = main_table.mageproduct_id',
                            ['value' => 'value']
                        );
                        if (count($sellerArr[$value['seller_id']])) {
                            $sellerProductColl->getSelect()->where(
                                'cpei.value=4
                                AND main_table.mageproduct_id NOT IN ('.implode(',', $sellerArr[$value['seller_id']]).')
                                AND cpei.attribute_id = '.$proAttId.'
                                AND cpei.store_id = 0'
                            );
                        } else {
                            $sellerProductColl->getSelect()->where(
                                'cpei.value=4
                                AND cpei.attribute_id = '.$proAttId.'
                                AND cpei.store_id = 0'
                            );
                        }
                        $remaingCount = 3 - count($sellerArr[$value['seller_id']]);
                        $sellerProductColl->getSelect()->limit($remaingCount);
                        foreach ($sellerProductColl as $value) {
                            if ($sellerProCount < 3) {
                                array_push(
                                    $sellerArr[$value['seller_id']],
                                    $value['mageproduct_id']
                                );
                                ++$sellerProCount;
                            }
                        }
                    }
                }
            }
        }
       

        if (count($sellerArr) != 4) {
            $i = count($sellerArr);
            // echo "<br> Seller Array total : ".count($sellerArr);
            $countProArr = [];
            $sellerProductColl = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->getCollection();

            
            if (count($sellerArr)) {
                $sellerProductColl->getSelect()
                ->join(
                    ['mmu' => $marketplaceUserdata],
                    'mmu.seller_id = main_table.seller_id',
                    ['is_seller' => 'is_seller']
                )->where(
                    'main_table.status!=0 
                    AND mmu.is_seller = 1 '
                );

            } else {
                $sellerProductColl->getSelect()
                ->join(
                    ['mmu' => $marketplaceUserdata],
                    'mmu.seller_id = main_table.seller_id',
                    ['is_seller' => 'is_seller']
                )->where('main_table.status!=0 AND mmu.is_seller = 1');
            }

                        

            if ($helper->getCustomerSharePerWebsite()) {
                $sellerProductColl->getSelect()->join(
                    $joinTable.' as cgf',
                    'main_table.seller_id = cgf.entity_id AND website_id= '.$websiteId
                );
            } else {
                $sellerProductColl->getSelect()->join(
                    $joinTable.' as cgf',
                    'main_table.seller_id = cgf.entity_id'
                );
            }

            

            

            $sellerProductColl->getSelect()
                             ->columns('COUNT(*) as countOrder')
                             ->group('seller_id');
            foreach ($sellerProductColl as $value) {
                if (!isset($countProArr[$value['seller_id']])) {
                    $countProArr[$value['seller_id']] = [];
                }
                $countProArr[$value['seller_id']] = $value['countOrder'];
            }


            arsort($countProArr);

        }
        $sellerProfileArr =  [];
        foreach ($sellerIdsArr as $sellerId) {
            $sellerData = $helper->getSellerCollectionObj($sellerId);
            foreach ($sellerData as $sellerDataResult) {
                $sellerId = $sellerDataResult->getSellerId();
                $sellerProfileArr[$sellerId] = [];
                $profileurl = $sellerDataResult->getShopUrl();
                $shoptitle = $sellerDataResult->getShopTitle();
                $logo = $sellerDataResult->getLogoPic()??"noimage.png";
                array_push(
                    $sellerProfileArr[$sellerId],
                    [
                        'profileurl'=>$profileurl,
                        'shoptitle'=>$shoptitle,
                        'logo'=>$logo
                    ]
                );
            }
        }
        
        return [$sellerArr, $sellerProfileArr, $sellerCountArr];
    }
}
