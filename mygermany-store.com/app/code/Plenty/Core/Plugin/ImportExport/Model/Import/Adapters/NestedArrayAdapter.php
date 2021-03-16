<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\Import\Adapters;

/**
 * Class NestedArrayAdapter
 * @package Plenty\Core\Plugin\ImportExport\Model\Import\Adapters
 */
class NestedArrayAdapter extends ArrayAdapter
{
    /**
     * @var string
     */
    protected $multipleValueSeparator;

    /**
     * NestedArrayAdapter constructor.
     * @param array|null $data
     * @param string $multipleValueSeparator
     */
    public function __construct(
        ?array $data,
        $multipleValueSeparator = ','
    ) {
        $this->multipleValueSeparator = $multipleValueSeparator;
        parent::__construct($data);

        foreach ($this->_array as &$row) {
            foreach ($row as $colName => &$value) {
                if (!is_array($value)) {
                    continue;
                }
                $this->_implode($value);
            }
        }
    }

    /**
     * @param $line
     */
    private function _implode(&$line)
    {
        $implodeStr = $this->multipleValueSeparator;
        $arr = array_map(
            function ($value, $key) use (&$implodeStr) {
                if (is_array($value) && is_numeric($key)) {
                    $this->_implode($value);
                    $implodeStr = '|';
                    return $value;
                }
                return sprintf("%s=%s", $key, $value);
            },
            $line,
            array_keys($line)
        );

        $line = implode($implodeStr, $arr);
    }
}
