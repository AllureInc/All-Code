<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Model;

use Webkul\MpPushNotification\Api\Data\TemplatesInterface;
use Webkul\MpPushNotification\Model\ResourceModel\Templates\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class TemplatesRepository implements \Webkul\MpPushNotification\Api\TemplatesRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpPushNotification\Model\ResourceModel\Ebayaccounts
     */
    protected $_resourceModel;

    public function __construct(
        TemplatesFactory $templatesFactory,
        \Webkul\MpPushNotification\Model\ResourceModel\Templates\CollectionFactory $collectionFactory,
        \Webkul\MpPushNotification\Model\ResourceModel\Templates $resourceModel,
        \Webkul\MpPushNotification\Helper\Data $helper
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_templatesFactory = $templatesFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_helper = $helper;
    }
    
    /**
     * get collection by template id
     * @param  int $templateId
     * @return object
     */
    public function getById($templateId)
    {
        $templateObject = $this->_templatesFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('entity_id', ['eq'=>$templateId])->getFirstItem();
        return $templateObject;
    }

    /**
     * get collection by template id
     * @param  int $templateId
     * @return TemplatesInterface
     */
    public function getBySellerId($sellerId)
    {
        return $this->_collectionFactory->create()
                ->addFieldToFilter('seller_id', ['eq'=>$sellerId]);
    }

    /**
     * get by ids
     * @param  array  $ids
     * @return UsersTokenInterface
     */
    public function getByIds(array $ids)
    {
        $sellerId = $this->_helper->getSellerId();
        return $this->_collectionFactory
            ->create()
            ->addFieldToFilter('seller_id', ['eq'=>$sellerId])
            ->addFieldToFilter('entity_id', ['in'=>$ids]);
    }
}
