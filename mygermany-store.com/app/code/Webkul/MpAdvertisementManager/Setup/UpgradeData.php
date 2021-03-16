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
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;
    /**
     * Logging instance
     * @var \Webkul\MpAdvertisementManager\Logger\AdsLogger
     */
    protected $_logger;
    /**
     * __construct
     *
     * @param \Magento\Catalog\Model\ProductFactory     $productFactory
     * @param \Magento\Framework\App\State              $appstate
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\State $appstate,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Webkul\MpAdvertisementManager\Logger\AdsLogger $logger
    ) {
        $this->_productFactory = $productFactory;
        $this->appState = $appstate;
        $this->_objectManager = $objectmanager;
        $this->_logger = $logger;
    }
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            try {
                $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
            $productModel= $this->_productFactory->create()->loadByAttribute('sku', 'wk_mp_ads_plan');
            $fileDriver = $this->_objectManager->get('Magento\Framework\Filesystem\Driver\File');
            $directory_list = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
            $sampleFilePath = $directory_list->getPath('app')."/code/Webkul/MpAdvertisementManager/view/base/web/images/wk-mp-ads-image.png";
            if (!$fileDriver->isExists($sampleFilePath)) {
                $sampleFilePath = $directory_list->getRoot()."/vendor/webkul/marketplace-advertisement-manager/src/app/code/Webkul/MpAdvertisementManager/view/base/web/images/wk-mp-ads-image.png";
            }
            if (!$fileDriver->isExists($sampleFilePath)) {
                $this->_logger->info('Advertisment product image path does not exist.--'.$sampleFilePath);
            } else {
                $mediaPath = $directory_list->getPath('media');
                $this->copyImageToMediaDirectory($sampleFilePath, $mediaPath);
                $mediaPath =$mediaPath."/wk-mp-ads-image.png";
                $mediaUrl = "wk-mp-ads-image.png";
                if ($fileDriver->isExists($mediaPath)) {
                    $productModel->addImageToMediaGallery($mediaUrl, ['image', 'small_image', 'thumbnail'], false, true);
                    $productModel->save();
                }
            }
        }
    }
    /**
     * copyImageToMediaDirectory function copy image to media directory
     *
     * @param string $sampleFilePath
     * @param string $mediaPath
     * @return void
     */
    public function copyImageToMediaDirectory($sampleFilePath, $mediaPath)
    {
        try {
            $imagePath = $sampleFilePath;
            $newPath = $mediaPath;
            $ext = '.png';
            $newName  = $newPath."/wk-mp-ads-image".$ext;
            $copied = copy($imagePath, $newName);
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }
}
