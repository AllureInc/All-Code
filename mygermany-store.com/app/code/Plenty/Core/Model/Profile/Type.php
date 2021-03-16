<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile;

use Plenty\Core\Model\Profile;
use Plenty\Core\Model\ProfileTypes\ConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 * @package Plenty\Core\Model\Profile
 */
class Type implements OptionSourceInterface
{
    /**
     * Available profile types
     */
    const TYPE_ATTRIBUTE            = 'attribute';
    const TYPE_CATEGORY             = 'category';
    const TYPE_CUSTOMER             = 'customer';
    const TYPE_PRODUCT              = 'product';
    const TYPE_ORDER                = 'order';
    const TYPE_STOCK                = 'stock';

    /**
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * profile types
     *
     * @var array|string
     */
    protected $_types;

    /**
     * Composite profile type Ids
     *
     * @var array
     */
    protected $_compositeTypes;


    /**
     * @var Type\Pool
     */
    protected $_profileTypePool;

    /**
     * Type constructor.
     * @param ConfigInterface $config
     * @param Type\Pool $profilePool
     */
    public function __construct(
        ConfigInterface $config,
        Type\Pool $profilePool
    ) {
        $this->_config = $config;
        $this->_profileTypePool = $profilePool;
    }

    /**
     * @param Profile $profile
     * @return Type\AbstractType
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function factory(Profile $profile)
    {
        $types = $this->getTypes();
        $adaptor = $profile->getAdaptor();
        $entity = $profile->getEntity();

        if (!isset($types[$entity]['adapter'][$adaptor]['model'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unknown profile entity type for %1 %2', $adaptor, $entity));
        }

        $typeModelName = $types[$entity]['adapter'][$adaptor]['model'];
        $typeModel = $this->_profileTypePool->get($typeModelName);
        $typeModel->setProfile($profile);
        $typeModel->setConfig($types[$entity]['adapter'][$adaptor]);
        return $typeModel;
    }

    /**
     * Get profile type labels array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getTypes() as $typeId => $type) {
            $options[$typeId] = (string)$type['label'];
        }
        return $options;
    }

    /**
     * Get profile type labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Get profile type labels array with empty value for option element
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get profile type labels array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get profile type label
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return $options[$optionId] ?? null;
    }

    /**
     * Get profile types
     *
     * @return array
     */
    public function getTypes()
    {
        if ($this->_types === null) {
            $profileTypes = $this->_config->getAll();
            foreach ($profileTypes as $profileTypeKey => $profileTypeConfig) {
                $profileTypes[$profileTypeKey]['label'] = __($profileTypeConfig['label']);
            }
            $this->_types = $profileTypes;
        }
        return $this->_types;
    }

    /**
     * @param Profile $profile
     * @return string|null
     */
    public function getRouter(Profile $profile)
    {
        $options = $this->getTypes();
        return isset($options[$profile->getEntity()]['adapter'][$profile->getAdaptor()]['router']) ?
            $options[$profile->getEntity()]['adapter'][$profile->getAdaptor()]['router'] : null;
    }


    /**
     * Return composite profile type Ids
     *
     * @return array
     */
    public function getCompositeTypes()
    {
        if ($this->_compositeTypes === null) {
            $this->_compositeTypes = [];
            $types = $this->getTypes();
            foreach ($types as $typeId => $typeInfo) {
                if (array_key_exists('composite', $typeInfo) && $typeInfo['composite']) {
                    $this->_compositeTypes[] = $typeId;
                }
            }
        }
        return $this->_compositeTypes;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
