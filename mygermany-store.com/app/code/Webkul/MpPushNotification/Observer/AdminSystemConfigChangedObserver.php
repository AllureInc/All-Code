<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Webkul MpPushNotification admin_system_config_changed_section_mppushnotification Observer.
 */
class AdminSystemConfigChangedObserver implements ObserverInterface
{

    public function __construct(
        RequestInterface $requestInterface,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Filesystem $fileSystem
    ) {
    
        $this->_request = $requestInterface;
        $this->_dir = $dir;
        $this->_fileSystem = $fileSystem;
    }

    /**
     * admin_system_config_changed_section_mppushnotification event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $manifestData = [];
        $data =  $this->_request->getParams();
        $fields = $data['groups']['mppushnotification']['fields'];
        try {
            if (!empty($fields['serverkey']['value']) && !empty($fields['senderid']['value'])) {
                $manifestData['name'] = 'Webkul Push Notification';
                $manifestData['gcm_sender_id'] = $fields['senderid']['value'];
                $jsonFileName = 'manifest.json';
                $mageDir = '/code/Webkul/MpPushNotification/view/frontend/web/json/manifest.json';
                
                $writer = $this->_fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);

                $file = $writer->openFile($mageDir, 'w+');
                try {
                    $file->lock();
                    try {
                        $file->write(json_encode($manifestData));
                    } finally {
                        $file->unlock();
                    }
                } finally {
                    $file->close();
                }
            }
        } catch (\Exception $e) {
                $e = $e->getMessage();
        }
    }
}
