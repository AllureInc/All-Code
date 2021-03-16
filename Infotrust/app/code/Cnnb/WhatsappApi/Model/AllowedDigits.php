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

class AllowedDigits extends AbstractModel implements IdentityInterface
{
    /**
     * Constatnt CACHE_TAG
     */
    const CACHE_TAG = 'config_changed_tbl';

    /**
     * @var $_cacheTag
     */
    protected $_cacheTag = 'config_changed_tbl';

    protected function _construct()
    {
        $this->_init('Cnnb\WhatsappApi\Model\ResourceModel\AllowedDigits');
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
