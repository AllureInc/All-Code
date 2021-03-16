<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Api;

use Webkul\MpPushNotification\Api\Data\TemplatesInterface;

/**
 * MpPushNotification template CRUD interface.
 * @api
 */
interface TemplatesRepositoryInterface
{

    /**
     * get collection by template id
     * @param  int $templateId
     * @return TemplatesInterface
     */
    public function getById($templateId);

    /**
     * get collection by template id
     * @param  int $templateId
     * @return TemplatesInterface
     */
    public function getBySellerId($sellerId);

    /**
     * get by ids
     * @param  array  $ids
     * @return UsersTokenInterface
     */
    public function getByIds(array $ids);
}
