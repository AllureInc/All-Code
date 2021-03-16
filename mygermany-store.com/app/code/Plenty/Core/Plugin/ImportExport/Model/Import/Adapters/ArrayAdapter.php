<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\Import\Adapters;

use Magento\ImportExport\Model\Import\AbstractSource;

/**
 * Class ArrayAdapter
 * @package Plenty\Core\Plugin\ImportExport\Model\Import\Adapters
 */
class ArrayAdapter extends AbstractSource
{
    /**
     * @var int
     */
    protected $_position = 0;

    /**
     * @var array The Data; Array of Array
     */
    protected $_array = [];

    /**
     * ArrayAdapter constructor.
     * @param array|null $data
     */
    public function __construct(?array $data)
    {
        $this->_array = $data;
        $this->_position = 0;
        $colNames = @array_keys($this->current());
        parent::__construct($colNames);
    }

    /**
     * Go to given position and check if it is valid
     *
     * @throws \OutOfBoundsException
     * @param int $position
     * @return void
     */
    public function seek($position)
    {
        $this->_position = $position;

        if (!$this->valid()) {
            throw new \OutOfBoundsException("invalid seek position ($position)");
        }
    }

    /**
     * Rewind to starting position
     *
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Get data at current position
     *
     * @return mixed
     */
    public function current()
    {
        return $this->_array[$this->_position];
    }

    /**
     * Get current position
     *
     * @return int
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * Set pointer to next position
     *
     * @return void
     */
    public function next()
    {
        ++$this->_position;
    }

    /**
     * Is current position valid?
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->_array[$this->_position]);
    }

    /**
     * Column names getter.
     *
     * @return array
     */
    public function getColNames()
    {
        $colNames = [];
        foreach ($this->_array as $row) {
            foreach (array_keys($row) as $key) {
                if (!is_numeric($key) && !isset($colNames[$key])) {
                    $colNames[$key] = $key;
                }
            }
        }
        return $colNames;
    }

    public function setValue($key, $value)
    {
        if (!$this->valid()) {
            return;
        }

        $this->_array[$this->_position][$key] = $value;
    }

    public function unsetValue($key)
    {
        if (!$this->valid()) {
            return;
        }

        unset($this->_array[$this->_position][$key]);
    }

    protected function _getNextRow()
    {
        $this->next();
        return $this->current();
    }
}
