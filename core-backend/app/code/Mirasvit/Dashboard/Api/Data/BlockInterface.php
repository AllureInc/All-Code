<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.2.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Api\Data;

interface BlockInterface
{
    const ID = 'block_id';
    const POS = 'pos';
    const SIZE = 'size';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    //
    //    const OPTIONS = 'options';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $value
     * @return $this
     */
    public function setId($value);

    /**
     * @return array
     */
    public function getPos();

    /**
     * @param array $pos
     * @return $this
     */
    public function setPos($pos);

    /**
     * @return array
     */
    public function getSize();

    /**
     * @param array $size
     * @return $this
     */
    public function setSize($size);
    //
    //    /**
    //     * @return string
    //     */
    //    public function getIdentifier();
    //
    //    /**
    //     * @param string $value
    //     * @return $this
    //     */
    //    public function setIdentifier($value);
    //
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle($value);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $value
     * @return $this
     */
    public function setDescription($value);

    /**
     * @param string $key
     * @return array|string|int
     */
    public function getData($key);

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key = '');

    /**
     * @param string $key
     * @param array|string|int $value
     * @return $this
     */
    public function setData($key, $value);

    /**
     * @param string $key
     * @return $this
     */
    public function unsetData($key);

    /**
     * @return array
     */
    public function asArray();

    /**
     * @param string $key
     * @param string|array $value
     * @return $this
     */
    public function setDataUsingMethod($key, $value);

    //    /**
    //     * @param string $key
    //     * @return array|string
    //     */
    //    public function getOption($key = null);
    //
    //    /**
    //     * @param string|array $key
    //     * @param string|int|array $value
    //     * @return $this
    //     */
    //    public function setOption($key, $value = null);
}