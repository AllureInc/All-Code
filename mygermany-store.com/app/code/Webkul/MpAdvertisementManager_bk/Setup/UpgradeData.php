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
    protected $_appState;


    /**
     * __construct
     *
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\State          $appstate
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\State $appstate
    ) {
        $this->_productFactory = $productFactory;
        $this->_appState = $appstate;
    }

    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.3') < 0) {
                $appState = $this->_appState;
                try{
                    $appState->setAreaCode('adminhtml');
                }catch(\Exception $e){

                }
                $productModel= $this->_productFactory->create()->loadByAttribute('sku', 'wk_mp_ads_plan');
                $objManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileDriver = $objManager->get('Magento\Framework\Filesystem\Driver\File');
                $directory_list = $objManager->get('Magento\Framework\App\Filesystem\DirectoryList');
                $sampleFilePath = $directory_list->getPath('app')."/code/Webkul/MpAdvertisementManager/view/base/web/images/wk-mp-ads-image.png";
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
            $e->getMessage();
        }
    }
}
