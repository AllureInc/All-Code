<?php
/**
 * Mangoit Software
 *
 * @category  Mangoit_Advertisement
 * @package   Mangoit_Advertisement
 * @author    Mangoit_Advertisement
 * @copyright Copyright (c) 2010-2017 Mangoit Software Private Limited
 */
namespace Mangoit\Advertisement\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\App\Bootstrap;

class UpgradeData implements UpgradeDataInterface
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
        \Magento\Framework\App\State $appstate,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_productModel = $productModel;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_eavConfig = $eavConfig;
        $this->_appState = $appstate;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->_eavConfig->clear();
            $appState = $this->_appState;
            $appState->setAreaCode('adminhtml');
            $product = $this->_productFactory->create();
            $this->registry->register('isSecureArea', true);
            try {
                $this->productRepository->deleteById('mis_adv_preview_product');
            } catch (\Exception $e) {
            }
            $attributeSetId = $this->_productModel->getDefaultAttributeSetId();
            $bootstrap = Bootstrap::create(BP, $_SERVER);
            $objectManager = $bootstrap->getObjectManager();

            $state = $objectManager->get('Magento\Framework\App\State');
            $state->setAreaCode('frontend');


            $_product = $objectManager->create('Magento\Catalog\Model\Product');
            $_product->setName('Ads Preview');
            $_product->setTypeId($this->_productType);
            $_product->setAttributeSetId($attributeSetId);
            $_product->setSku('mis_adv_preview_product');
            $_product->setWebsiteIds([$this->_storeManager->getDefaultStoreView()->getWebsiteId()]);
            $_product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
            $_product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

            $_product->setStockData(array(
                    'use_config_manage_stock' => 0, //'Use config settings' checkbox
                    'manage_stock' => 1, //manage stock
                    'is_in_stock' => 1, //Stock Availability
                    'qty' => 10000 //qty
                    )
                );

            $_product->save();
        }
    }
}
