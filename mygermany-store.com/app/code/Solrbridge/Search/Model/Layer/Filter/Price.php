<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */
namespace Solrbridge\Search\Model\Layer\Filter;

use Solrbridge\Search\Helper\Filter as FilterHelper;

class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price
{
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Search\Dynamic\Algorithm
     */
    private $priceAlgorithm;
    
    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        array $data = []
    ) {
        $this->_requestVar = 'price';
        $this->priceCurrency = $priceCurrency;
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->priceAlgorithm = $priceAlgorithm;
        
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );
    }
    /**
     * Apply attribute option filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $attributeValues = FilterHelper::getParam($this->_requestVar);
        if (empty($attributeValues) || count($attributeValues) < 1) {
            return $this;
        }
        
        $attribute = $this->getAttributeModel();
        
        foreach ($attributeValues as $attributeValue) {
            $data = explode('-', $attributeValue);
            
            $from = $data[0];
            $to = $data[1];
            
            $label = $this->renderRangeLabel(empty($from) ? 0 : $from, $to);
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($label, $attributeValue));
        }
        
        //$this->setItems([]); // set items to disable show filtering
        return $this;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        //Note: the key price was set from Model/Layer/FilterList.php
        $facets = $this->getFacetPriceRanges();
        
        $data = [];
        if (count($facets) > 0) { // two range minimum
            foreach ($facets as $key => $aggregation) {
                $key = $aggregation['from'].'_'.$aggregation['to'];
                $count = $aggregation['count'];
                $data[] = $this->prepareData($key, $count, $aggregation);
            }
        }

        return $data;
    }
    
    /**
     * @param string $key
     * @param int $count
     * @return array
     */
    private function prepareData($key, $count, $data)
    {
        list($from, $to) = explode('_', $key);
        $from = $data['from'];
        $to = $data['to'];
        
        $label = $this->renderRangeLabel(
            empty($from) ? 0 : $from,
            empty($to) ? $to : $to
        );
        //$value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();
        $value = $from . '-' . $to;

        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }
    
    /**
     * Prepare text of range label
     *
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return float|\Magento\Framework\Phrase
     */
    protected function renderRangeLabel($fromPrice, $toPrice)
    {
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        //$formattedFromPrice = floor($formattedFromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()
        ) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                //$toPrice -= .01;
            }

            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }
    
    protected function getSolrData()
    {
        $attribute = $this->getAttributeModel();
        return $attribute->getData('solr_data');
    }
    
    protected function _calculatePriceRanges($step = 10, $gap = 10, $min = 0, $max = 0)
    {
        $ranges = [];
        
        
        $from = $min;
        $appliedFlag = true;
        
        if ($max > 0) {
            for ($i = 0; $i < 10; $i++) {
                if (!$appliedFlag) {
                    //var_dump($from);exit();
                }
                if ($i > 0 && $appliedFlag) {
                    //$from = $from + $gap;
                }
                $to = ($gap * $i) + $gap;
                if ($to > $max) {
                    $to = $max;
                }
                $range = [
                    'from' => $from,
                    'to' => $to
                ];
                
                $range = $this->_applyPriceRangeProductCount($range);
                if (!isset($range['count']) || $range['count'] < 1) {
                    //$from = $from - $gap;
                    $appliedFlag = false; continue;
                }
                $from = $to;
                $appliedFlag = true;
                $ranges[] = $range;
            }
        }
        
        return $ranges;
    }
    
    /**
     * Calculate price ranges
     * @param array $priceRanges
     * @param decimal $min
     * @param decimal $max
     * @return array:
     */
    protected function calculatePriceRanges()
    {
        $solrData = $this->getSolrData();

        $priceFieldName = $this->getPriceFieldName();

        $priceRanges = array();

        $max = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['max'])) {
            $max = $solrData['stats']['stats_fields'][$priceFieldName]['max'];
        }
        
        $min = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['min'])) {
            $min = $solrData['stats']['stats_fields'][$priceFieldName]['min'];
        }
        
        $step = 10;
        $gap = $max / $step;
        
        return $this->_calculatePriceRanges($step, $gap, $min, $max);
        
        $gap = $max / $step;
        
        for ($i = 0; $i <= $step; $i++) {
            $priceRanges[] = [
                'from' => ($gap * $i),
                'to' => ($i + $gap + $gap)
            ];
        };
        
        return $priceRanges;
        /*
        if ($max <= 100) {
            
        } else if ($max > 100 && <= 200) {
            
        } else if ($max > 200 && <= 300) {
            
        } else if ($max > 300 && <= 400) {
            
        } else if ($max > 400 && <= 500) {
            
        } else if ($max > 500 && <= 600) {
            
        } else if ($max > 600 && <= 700) {
            
        } else if ($max > 700 && <= 800) {
            
        } else if ($max > 800 && <= 900) {
            
        } else if ($max > 900 && <= 1000) {
            
        } else if ($max > 1000 && <= 5000) {
            
        } else if ($max > 5000 && <= 10000) {
            
        } else if ($max > 10000 && <= 20000) {
            
        } else if ($max > 20000 && <= 30000) {
            
        } else if ($max > 20000 && <= 30000) {
            
        }*/
        
        return $priceRanges;
    }
    
    /**
     * Calculate price ranges
     * @param array $priceRanges
     * @param decimal $min
     * @param decimal $max
     * @return array:
     */
    protected function calculatePriceRangesOLD()
    {
        $solrData = $this->getSolrData();

        $priceFieldName = $this->getPriceFieldName();

        $priceRanges = array();

        if (isset($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts']) &&
            is_array($solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'])) {
            $priceRanges = $solrData['facet_counts']['facet_ranges'][$priceFieldName]['counts'];
        }

        $min = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['min'])) {
            $min = $solrData['stats']['stats_fields'][$priceFieldName]['min'];
        }

        $max = 0.0;
        if (isset($solrData['stats']['stats_fields'][$priceFieldName]['max'])) {
            $max = $solrData['stats']['stats_fields'][$priceFieldName]['max'];
        }

        $tempPriceRanges = array();
        $tempPriceRanges[] = $min;
        if (is_array($priceRanges)) {
            $index = 0;
            foreach ($priceRanges as $key => $value) {
                if ($index > 0) {
                    $tempPriceRanges[] = $key;
                }
                $index++;
            }
        }
        //$tempPriceRanges[] = $max;

        $returnPriceRanges = array();
        $index = 0;
        foreach ($tempPriceRanges as $index => $item) {
            $start = $item;
            $end = $item;

            if ($index == (count($tempPriceRanges) - 1)) {
                $end = $max;
            }
            
            if (isset($tempPriceRanges[($index + 1)])) {
                $end = ($tempPriceRanges[($index + 1)] - 1);
            }
            
            //if ($index < (count($tempPriceRanges) - 1)) {
            $returnPriceRanges[] = array('from' => $start, 'to' => $end);
                //}

            //$index++;
        }
        
        if ($max > 0 && count($returnPriceRanges) < 1) {
            $returnPriceRanges[] = array('from' => $min, 'to' => $max);
        }
        
        return $returnPriceRanges;
    }
    
    protected function getPriceFieldName()
    {
        return 'price_decimal';
    }
    
    protected function _applyPriceRangeProductCount($range)
    {
        $priceFieldName = $this->getPriceFieldName();

        $appliedPriceRanges = array();
        $solrData = $solrData = $this->getSolrData();
        $priceFacets = array();

        if (isset($solrData['facet_counts']['facet_fields'][$priceFieldName]) &&
            is_array($solrData['facet_counts']['facet_fields'][$priceFieldName])) {
            $priceFacets = $solrData['facet_counts']['facet_fields'][$priceFieldName];
        }

        //Need to update dynamic currency symbol
        $currencySign = '$';

        $currencyPositionSetting = 0;
        
        $start = floor(floatval($range['from']));
        $end = ceil(floatval($range['to']));
        
        $start = floor(floatval($range['from']));
        $end = ceil(floatval($range['to']));

        if ($currencyPositionSetting > 0) {
            $formatted = $currencySign.'&nbsp;'.$start.' - '.$currencySign.'&nbsp;'.$end;
        } else {
            $formatted = $start.'&nbsp;'.$currencySign.' - '.$end.'&nbsp;'.$currencySign;
        }

        $rangeItemArray = array(
                'from' => $start,
                'to' => $end,
                'count' => 0,
                'formatted' => $formatted,
                'value' => $start.' TO '.$end,
        );
        foreach ($priceFacets as $price => $count) {
            $price = floor($price);
            if (floatval($price) >= floatval($start) && floatval($price) <= floatval($end)) {
                $rangeItemArray['count'] = ($rangeItemArray['count'] + $count);
            }
        }

        return $rangeItemArray;
    }

    protected function applyPriceRangeProductCount()
    {
        $priceFieldName = $this->getPriceFieldName();

        $priceRanges = $this->calculatePriceRanges();

        $appliedPriceRanges = array();
        $solrData = $solrData = $this->getSolrData();
        $priceFacets = array();

        if (isset($solrData['facet_counts']['facet_fields'][$priceFieldName]) &&
            is_array($solrData['facet_counts']['facet_fields'][$priceFieldName])) {
            $priceFacets = $solrData['facet_counts']['facet_fields'][$priceFieldName];
        }

        //Need to update dynamic currency symbol
        $currencySign = '$';

        $currencyPositionSetting = 0;

        foreach ($priceRanges as $range) {
            $start = floor(floatval($range['from']));
            $end = ceil(floatval($range['to']));

            if ($currencyPositionSetting > 0) {
                $formatted = $currencySign.'&nbsp;'.$start.' - '.$currencySign.'&nbsp;'.$end;
            } else {
                $formatted = $start.'&nbsp;'.$currencySign.' - '.$end.'&nbsp;'.$currencySign;
            }

            $rangeItemArray = array(
                    'from' => $start,
                    'to' => $end,
                    'count' => 0,
                    'formatted' => $formatted,
                    'value' => $start.' TO '.$end,
            );
            foreach ($priceFacets as $price => $count) {
                $price = floor($price);
                if (floatval($price) >= floatval($start) && floatval($price) <= floatval($end)) {
                    $rangeItemArray['count'] = ($rangeItemArray['count'] + $count);
                }
            }

            $appliedPriceRanges[] = $rangeItemArray;
        }

        return $appliedPriceRanges;
    }
    
    public function getPriceFormat($price)
    {
        $formattedPrice = $price;
        $currencySign = '$';
        $currencyPositionSetting = 0;

        if ($currencyPositionSetting < 1) {
            //After
            $formattedPrice = $price.'&nbsp;'.$currencySign;
        } else {
            $formattedPrice = $currencySign.'&nbsp;'.$price;
        }
        return $formattedPrice;
    }

    public function getFacetPriceRanges()
    {
        return $this->calculatePriceRanges();
        return $this->applyPriceRangeProductCount();
    }
}
