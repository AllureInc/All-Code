<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
 
namespace Webkul\Walletsystem\Setup;

use Magento\Framework\Setup;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;

class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;
    /**
     * @var Installer
     */
    protected $_productType = \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $_catalogProductTypeManager;
    /**
     * @param \Magento\Catalog\Model\Product                $productModel
     * @param \Magento\Store\Model\StoreManagerInterface    $storeManager
     * @param \Magento\Catalog\Model\ProductFactory         $productFactory
     * @param \Magento\Eav\Model\Config                     $eavConfig
     * @param EavSetupFactory                               $eavSetupFactory
     * @param \Magento\Framework\App\State                  $appstate
     * @param \Magento\Framework\Module\Dir\Reader          $moduleReader
     * @param \Magento\Framework\Filesystem                 $filesystem
     */
    public function __construct(
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\State $appstate,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\TypeTransitionManager $catalogProductTypeManager
    ) {
        $this->_productModel = $productModel;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_eavConfig = $eavConfig;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_appState = $appstate;
        $this->moduleReader = $moduleReader;
        $this->_filesystem = $filesystem;
        $this->_catalogProductTypeManager = $catalogProductTypeManager;
    }

    public function install(
        Setup\ModuleDataSetupInterface $setup,
        Setup\ModuleContextInterface $moduleContext
    ) {
        $this->_eavConfig->clear();
        $appState = $this->_appState;
        $appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $product = $this->_productFactory->create();
        $store = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $attributeSetId = $this->_productModel->getDefaultAttributeSetId();
        
        $mageProduct = $this->_productFactory->create();
        $mageProduct->setAttributeSetId($attributeSetId);
        $mageProduct->setTypeId($this->_productType);
        $mageProduct->setStoreId($store);

        $requestData = [
            'product' => [
                'name' => 'Wallet Amount',
                'attribute_set_id' => $attributeSetId,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                'sku' => 'wk_wallet_amount',
                'tax_class_id' => 0,
                'description' => 'wallet amount',
                'short_description' => 'wallet amount',
                'stock_data' => [
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 0,
                    'is_decimal_divided' => 0
                ],
                'quantity_and_stock_status' => [
                    'qty' => 1,
                    'is_in_stock' => 1
                ]
            ]
        ];
        $catalogProduct = $this->productInitialize($mageProduct, $requestData);
        $this->_catalogProductTypeManager->processProduct($catalogProduct);
        if ($catalogProduct->getSpecialPrice() == '') {
            $catalogProduct->setSpecialPrice(null);
            $catalogProduct->getResource()->saveAttribute($catalogProduct, 'special_price');
        }
        $catalogProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->save();

        $imagePath = $this->getViewFileUrl().'walletsystem/wallet.png';

        $catalogProduct->addImageToMediaGallery($imagePath, ['image', 'small_image', 'thumbnail'], false, false);
        $catalogProduct->save();
    }
    public function getViewFileUrl()
    {
        return $this->_filesystem
                    ->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath();
    }
    private function productInitialize(\Magento\Catalog\Model\Product $catalogProduct, $requestData)
    {
        $requestProductData = $requestData['product'];
        $requestProductData['product_has_weight'] = 0;
        $catalogProduct->addData($requestProductData);
        $websiteIds = [];
        $allWebsites = $this->_storeManager->getWebsites();
        foreach ($allWebsites as $website) {
            $websiteIds[] = $website->getId();
        }
        $catalogProduct->setWebsiteIds($websiteIds);
        return $catalogProduct;
    }
}
