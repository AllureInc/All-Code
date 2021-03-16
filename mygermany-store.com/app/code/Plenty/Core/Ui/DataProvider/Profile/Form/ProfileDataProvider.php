<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Ui\DataProvider\Profile\Form;

use Plenty\Core\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class ProfileDataProvider
 * @package Plenty\Core\Ui\DataProvider\Profile\Form
 */
class ProfileDataProvider extends AbstractDataProvider
{

    /**
     * ProfileDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
