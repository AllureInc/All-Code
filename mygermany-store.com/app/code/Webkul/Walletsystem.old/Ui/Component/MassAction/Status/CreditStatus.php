<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 */
class CreditStatus implements JsonSerializable
{
    /**
     * @var array
     */
    protected $_options;
    /**
     * Additional options params
     *
     * @var array
     */
    protected $_data;
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;
    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $_urlPath;
    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $_paramName;
    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $_additionalData = [];

    /**
     * Constructor
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->_data = $data;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->_options === null) {
            $options = $this->getOptionArray();
            $this->prepareData();
            foreach ($options as $option) {
                $this->_options[$option['value']] = [
                    'type' => 'creditrule_status' . $option['value'],
                    'label' => $option['label'],
                ];
                $this->_options[$option['value']]['url'] = $this->_urlBuilder->getUrl(
                    $this->_urlPath,
                    [$this->_paramName => $option['value']]
                );
                $this->_options[$option['value']] = array_merge_recursive(
                    $this->_options[$option['value']],
                    $this->_additionalData
                );
            }
            $this->_options = array_values($this->_options);
        }
        return $this->_options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->_data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->_urlPath = $value;
                    break;
                case 'paramName':
                    $this->_paramName = $value;
                    break;
                default:
                    $this->_additionalData[$key] = $value;
                    break;
            }
        }
    }

    public static function getOptionArray()
    {
        $options = [
            [
                'value' => 1,
                'label' => 'Enabled'
            ], [
                'value' => 0,
                'label' => 'Disabled'
            ]
        ];
        return $options;
    }
}
