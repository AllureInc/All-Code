<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model;

use Scommerce\CookiePopup\Api\Data\LinkInterface;

/**
 * Class LinkRepository
 * @package Scommerce\CookiePopup\Model
 */
class LinkRepository implements \Scommerce\CookiePopup\Api\LinkRepositoryInterface
{
    /** @var \Magento\Framework\Api\DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var ResourceModel\Link */
    protected $resource;

    /** @var LinkFactory */
    protected $linkFactory;

    /**
     * LinkRepository constructor.
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param ResourceModel\Link $resource
     * @param LinkFactory $linkFactory
     */
    public function __construct(
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Scommerce\CookiePopup\Model\ResourceModel\Link $resource,
        \Scommerce\CookiePopup\Model\LinkFactory $linkFactory
    ) {
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->resource                 = $resource;
        $this->linkFactory              = $linkFactory;
    }

    /**
     * @param LinkInterface $model
     * @return bool
     */
    public function save(LinkInterface $model)
    {
        try {
            /** @var LinkInterface|\Magento\Framework\Model\AbstractModel $model */
            $this->resource->save($model);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the choice: %1', $e->getMessage()));
        }
        return true;
    }
}