<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Ui\Component\MassAction\Badge;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
 
/**
 * Class Assignoptions
 */
class Assignoptions implements JsonSerializable
{
    /**
     * @var array
     */
    protected $options;
 
    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;
 
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
 
    /**
     * Base URL for subactions
     *
     * @var string
     */
    protected $urlPath;
 
    /**
     * Param name for subactions
     *
     * @var string
     */
    protected $paramName;
 
    /**
     * Additional params for subactions
     *
     * @var array
     */
    protected $additionalData = [];
 
    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }
 
    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $i=0;
        if ($this->options === null) {
            // get the massaction data from the database table

            //make a array of massaction
                $opt[0]['value']= 0;
                $opt[0]['label']='disable';
                $opt[1]['value']= 1;
                $opt[1]['label']='enable';
            $this->prepareData();
            foreach ($opt as $optCode) {
                $this->options[$optCode['value']] = [
                    'type' => 'change_' . $optCode['value'],
                    'label' => $optCode['label'],
                ];
 
                if ($this->urlPath && $this->paramName) {
                    $this->options[$optCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optCode['value']]
                    );
                }
 
                $this->options[$optCode['value']] = array_merge_recursive(
                    $this->options[$optCode['value']],
                    $this->additionalData
                );
            }
             
            // return the massaction data
            $this->options = array_values($this->options);
        }
        return $this->options;
    }
 
    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
          
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
