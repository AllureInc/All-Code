<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Setup;

use Magento\Framework\Setup;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;
    
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
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * __construct
     *
     * @param \Magento\Catalog\Model\Product                  $productModel
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \Magento\Catalog\Model\ProductFactory           $productFactory
     * @param \Magento\Eav\Model\Config                       $eavConfig
     * @param EavSetupFactory                                 $eavSetupFactory
     * @param \Magento\Framework\App\State                    $appstate
     * @param ControllersRepository                           $controllersRepository
     * @param \Magento\Framework\Registry                     $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\State $appstate,
        ControllersRepository $controllersRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_productModel = $productModel;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_eavConfig = $eavConfig;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_appState = $appstate;
        $this->controllersRepository = $controllersRepository;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    public function install(
        Setup\ModuleDataSetupInterface $setup,
        Setup\ModuleContextInterface $moduleContext
    ) {
        /**
         * insert marketplace controller's data
         */
         $data = [];
        if (!count($this->controllersRepository->getByPath('mpads/block'))) {
            $data[] =
                [
                    'module_name' => 'Webkul_MpAdvertisementManager',
                    'controller_path' => 'mpads/block',
                    'label' => 'Ads Block',
                    'is_child' => '0',
                    'parent_id' => '0',
                ];
        }
        if (!count($this->controllersRepository->getByPath('mpads/advertise'))) {
            $data[] = [
                    'module_name' => 'Webkul_MpAdvertisementManager',
                    'controller_path' => 'mpads/advertise',
                    'label' => 'Advertise',
                    'is_child' => '0',
                    'parent_id' => '0',
                ];
        }

        $setup->getConnection()
            ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);

        $this->_eavConfig->clear();
        $appState = $this->_appState;
        $appState->setAreaCode('adminhtml');
        $product = $this->_productFactory->create();
        $this->registry->register('isSecureArea', true);
        try {
            $this->productRepository->deleteById('wk_mp_ads_plan');
        } catch (\Exception $e) {
        }
        $attributeSetId = $this->_productModel->getDefaultAttributeSetId();
        $product->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $product->setWebsiteIds([$this->_storeManager->getDefaultStoreView()->getWebsiteId()]);
        $product->setTypeId($this->_productType);
        $product->addData(
            [
                'name' => 'Ads Plan',
                'attribute_set_id' => $attributeSetId,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                'weight' => 1,
                'sku' => 'wk_mp_ads_plan',
                'tax_class_id' => 0,
                'description' => 'Seller Ads Plan',
                'short_description' => 'Seller Ads Plan',
                'stock_data' => [
                    'manage_stock' => 1,
                    'qty' => 999,
                    'is_in_stock' => 1
                ]
            ]
        );
        $product->save();
    }
}
