<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Model\Pricing;

use Magento\Eav\Model\Config;
use Webkul\MpAdvertisementManager\Model\Pricing;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\Collection;
use Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory as PricingCollectionFactory;

/**
 * Class DataProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param string                                    $name
     * @param string                                    $primaryFieldName
     * @param string                                    $requestFieldName
     * @param PricingCollectionFactory                  $pricingCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @param array                                     $meta
     * @param array                                     $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        PricingCollectionFactory $pricingCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $meta = [],
        array $data = []
    ) {
        $this->_objectManager = $objectmanager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $pricingCollectionFactory->create();
        $this->collection->addFieldToSelect('*');
    }

    /**
     * Get session object.
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = $this->_objectManager->get(
                'Magento\Framework\Session\SessionManagerInterface'
            );
        }

        return $this->session;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /**
        * @var Customer $pricing
        */
        foreach ($items as $pricing) {
            $result['ads_pricing'] = $pricing->getData();
            $this->loadedData[$pricing->getId()] = $result;
        }

        $data = $this->getSession()->getPricingFormData();
        if (!empty($data)) {
            $pricingId = isset($data['ads_pricing']['id'])
            ? $data['ads_pricing']['id'] : null;
            $this->loadedData[$pricingId] = $data;
            $this->getSession()->unsPricingFormData();
        }
        
        return $this->loadedData;
    }
}
