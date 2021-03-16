<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)      Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Api\Data;

interface TemplatesInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const TITLE = 'title';
    const MESSAGE = 'message';
    const URL = 'url';
    const LOGO = 'logo';
    const TAGS = 'tags';
    const CREATED_AT = 'created_at';

    /**
     * get id
     * @return string
     */
    public function getId();
   
    /**
     * set id
     * @param int $id
     * @return  TemplatesInterface
     */
    public function setId($id);

    /**
     * get title
     * @return string
     */
    public function getTitle();

    /**
     * set title
     * @param string $title
     * @return  TemplatesInterface
     */
    public function setTitle($title);

    /**
     * get message
     * @return string
     */
    public function getMessage();

    /**
     * set messge
     * @param string $message
     * @return  TemplatesInterface
     */
    public function setMessage($message);

    /**
     * get redirect url
     * @return string
     */
    public function getUrl();

    /**
     * set redirect url
     * @param string $url
     * @return  TemplatesInterface
     */
    public function setUrl($url);

    /**
     * get logo image
     * @return string
     */
    public function getLogo();

    /**
     * set logo
     * @param string $logo
     * @return  TemplatesInterface
     */
    public function setLogo($logo);

    /**
     * get tags
     * @return string
     */
    public function getTags();

    /**
     * set tags
     * @param string $tags
     * @return  TemplatesInterface
     */
    public function setTags($tags);

    /**
     * get created time
     * @return timestamp
     */
    public function getcreatedAt();

    /**
     * set created time
     * @param timestamp $createdAt
     * @return  TemplatesInterface
     */
    public function setCreatedAt($createdAt);
}
