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
use Magento\Framework\DataObject\IdentityInterface;

class Templates extends \Magento\Framework\Model\AbstractModel implements TemplatesInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'mp_pushnotification_templates';

    /**
     * @var string
     */
    protected $_cacheTag = 'mp_pushnotification_templates';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mp_pushnotification_templates';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpPushNotification\Model\ResourceModel\Templates');
    }

    /**
     * get id
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }
    
    /**
     * set id
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * get title
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * set title
     * @param string $title
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * get message
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * set messge
     * @param string $message
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * get redirect url
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * set redirect url
     * @param string $url
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * get logo image
     * @return string
     */
    public function getLogo()
    {
        return $this->getData(self::LOGO);
    }

    /**
     * set logo
     * @param string $logo
     */
    public function setLogo($logo)
    {
        return $this->setData(self::LOGO, $logo);
    }

    /**
     * get tags
     * @return string
     */
    public function getTags()
    {
        return $this->getData(self::TAGS);
    }

    /**
     * set tags
     * @param string $tags
     */
    public function setTags($tags)
    {
        return $this->setData(self::TAGS, $tags);
    }

    /**
     * get seller Id
     * @return string
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * set seller Id
     * @param int $sellerId
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * get created time
     * @return timestamp
     */
    public function getcreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * set created time
     * @param timestamp $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
