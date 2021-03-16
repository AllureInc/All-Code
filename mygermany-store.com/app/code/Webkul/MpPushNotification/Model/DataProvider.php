<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Model;

use Webkul\MpPushNotification\Model\ResourceModel\Templates\CollectionFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;

    protected $session;
 
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $employeeCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $employeeCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();

        /** @var Customer $bannerimage */
        foreach ($items as $template) {
            $result['template'] = $template->getData();
            $this->_loadedData[$template->getId()] = $result;
        }

        $data = $this->getSession()->getTemplateFormData();
        if (!empty($data)) {
            $templateId = isset($data['pushnotification_template']['entity_id'])
            ? $data['pushnotification_template']['entity_id'] : null;
            $this->_loadedData[$templateId] = $data;
            $this->getSession()->unsTemplateFormData();
        }

        return $this->_loadedData;
    }

    /**
     * Get session object.
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()->get(
                'Magento\Framework\Session\SessionManagerInterface'
            );
        }

        return $this->session;
    }
}
