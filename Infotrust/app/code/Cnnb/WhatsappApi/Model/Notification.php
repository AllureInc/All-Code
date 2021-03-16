<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Model Class
 */

namespace Cnnb\WhatsappApi\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Notification extends AbstractModel implements IdentityInterface
{
    /**
     * Constatnt CACHE_TAG
     */
    const CACHE_TAG = 'whatsapp_notification';

    /**
     * @var $_cacheTag
     */
    protected $_cacheTag = 'whatsapp_notification';

    protected function _construct()
    {
        $this->_init('Cnnb\WhatsappApi\Model\ResourceModel\Notification');
    }

    /**
     * Function for getting cache tag
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Function for getting default values
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
