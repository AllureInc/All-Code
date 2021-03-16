<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Ui\Component\MassAction;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use Mangoit\RakutenConnector\Model\Config\Source\CategoriesList;

/**
 * Class Options.
 */
class AllStoreCategoryList implements JsonSerializable
{
    /**
     * @var array
     */
    protected $_asinCatOptions;

    /**
     * @var CategoriesList
     */
    protected $_categoriesList;

    /**
     * Additional options params.
     *
     * @var array
     */
    protected $_data;

    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Base URL for assign product to category.
     * @var string
     */
    protected $_urlPath;

    /**
     * Param name for category id.
     *
     * @var string
     */
    protected $_paramName;

    /**
     * Additional params for assign category to product.
     *
     * @var array
     */
    protected $_additionalData = [];

    /**
     * Constructor.
     * @param CategoriesList $categoriesList
     * @param UrlInterface   $urlBuilder
     * @param array          $data
     */
    public function __construct(
        CategoriesList $categoriesList,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
    
        $this->_categoriesList = $categoriesList;
        $this->_data = $data;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Get action asinCatOptions.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->_asinCatOptions === null) {
            $asinCatOptions = $this->_categoriesList->toOptionArray();
            $this->prepareAssignCatActionData();
            foreach ($asinCatOptions as $optCatCode) {
                if ($optCatCode['value']) {
                    $this->_asinCatOptions[$optCatCode['value']] = [
                        'type' => 'cat_id_'.$optCatCode['value'],
                        'label' => $optCatCode['label'],
                    ];

                    if ($this->_urlPath && $this->_paramName) {
                        $this->_asinCatOptions[$optCatCode['value']]['url'] = $this->_urlBuilder->getUrl(
                            $this->_urlPath,
                            [$this->_paramName => $optCatCode['value']]
                        );
                    }

                    $this->_asinCatOptions[$optCatCode['value']] = array_merge_recursive(
                        $this->_asinCatOptions[$optCatCode['value']],
                        $this->_additionalData
                    );
                }
            }

            $this->_asinCatOptions = array_values($this->_asinCatOptions);
        }

        return $this->_asinCatOptions;
    }

    /**
     * Prepare addition data for assign category to product.
     */
    protected function prepareAssignCatActionData()
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
