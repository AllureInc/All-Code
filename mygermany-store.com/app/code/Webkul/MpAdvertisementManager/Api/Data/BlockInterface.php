<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Api\Data;

/**
 * MpAdvertisementManager Block interface.
 *
 * @api
 */
interface BlockInterface
{

    const ENTITY_ID = 'id';

    const CONTENT = 'content';

    const ADDED_BY = 'added_by';

    const SELLER_ID = 'seller_id';

    const CREATED_AT = 'created_at';
    
    const UPDATED_AT = 'updated_at';



    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param  int $id
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setId($id);

    /**
     * Get seller id
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Set seller id
     *
     * @param  int $sellerId
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setSellerId($sellerId);

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Set Content
     *
     * @param  string $content
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setContent($content);

    /**
     * Get AddedBy
     *
     * @return string|null
     */
    public function getAddedBy();

    /**
     * Set AddedBy
     *
     * @param  string $addedBy
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setAddedBy($addedBy);

    /**
     * Get created date.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created date.
     *
     * @param string $createdAt
     *
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated date.
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated date.
     *
     * @param string $updatedAt
     *
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockInterface
     */
    public function setUpdatedAt($updatedAt);
}
