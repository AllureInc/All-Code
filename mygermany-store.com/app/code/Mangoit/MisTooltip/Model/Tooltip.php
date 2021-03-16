<?php
namespace Mangoit\MisTooltip\Model;
class Tooltip extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'mis_tooltip';

    protected $_cacheTag = 'mis_tooltip';

    protected $_eventPrefix = 'mis_tooltip';

    protected function _construct()
    {
        $this->_init('Mangoit\MisTooltip\Model\ResourceModel\Tooltip');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}