<?php
/**
 * Copyright © Mangoit, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Productfaq\Model\Page\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class Stores implements OptionSourceInterface
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(\Magento\Cms\Model\Page $cmsPage,
        \Mangoit\Marketplace\Helper\Data $helper)
    {
        $this->cmsPage = $cmsPage;
        $this->helper = $helper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $stores = $this->helper->getAllStores();
        $availableOptions = $this->cmsPage->getAvailableStatuses();
        $options = [];
        foreach ($stores as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
