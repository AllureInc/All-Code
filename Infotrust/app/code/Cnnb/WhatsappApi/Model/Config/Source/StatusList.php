<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Model class
 */

namespace Cnnb\WhatsappApi\Model\Config\Source;

use Magento\Framework\App\ObjectManager;
use Cnnb\WhatsappApi\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    protected $statusCollectionFactory;

    public function toOptionArray()
    {
        $this->statusCollectionFactory = ObjectManager::getInstance()->get(CollectionFactory::class);
        $options = $this->statusCollectionFactory->create()->toOptionArray();
        return $options;
    }
}
