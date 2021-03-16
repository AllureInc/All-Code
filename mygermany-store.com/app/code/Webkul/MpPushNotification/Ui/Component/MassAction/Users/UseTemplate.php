<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Ui\Component\MassAction\Users;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use Webkul\MpPushNotification\Model\ResourceModel\Templates\CollectionFactory;

/**
 * Class Options
 */
class UseTemplate implements JsonSerializable
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

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
     *
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
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
            $templatesColl = $this->_collectionFactory->create()
                        ->addFieldToFilter('seller_id', ['eq'=>0]);
            $i=0;
            if (!$templatesColl->getSize()) {
                return $this->_options;
            }
            foreach ($templatesColl as $key => $template) {
                $options[$i]['value']=$template->getEntityId();
                $options[$i]['label']=$template->getTitle();
                $i++;
            }
            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->_options[$optionCode['value']] = [
                    'type' => 'template_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];

                if ($this->_urlPath && $this->_paramName) {
                    $this->_options[$optionCode['value']]['url'] = $this->_urlBuilder->getUrl(
                        $this->_urlPath,
                        [$this->_paramName => $optionCode['value']]
                    );
                }

                $this->_options[$optionCode['value']] = array_merge_recursive(
                    $this->_options[$optionCode['value']],
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
}
