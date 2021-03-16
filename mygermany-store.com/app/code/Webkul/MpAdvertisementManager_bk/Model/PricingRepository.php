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

namespace Webkul\MpAdvertisementManager\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaInterface;
use Webkul\MpAdvertisementManager\Api\Data\PricingInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class PricingRepository implements \Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface
{
    /**
     * @var PricingFactory
     */
    protected $_pricingFactory;

    /**
     * @var Pricing[]
     */
    protected $_instances = [];

    /**
     * @var Pricing[]
     */
    protected $_instancesById = [];

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @param PricingFactory                                       $pricingFactory
     * @param ResourceModel\Pricing\CollectionFactory              $collectionFactory
     * @param ResourceModel\Pricing                                $resourceModel
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PricingFactory $pricingFactory,
        ResourceModel\Pricing\CollectionFactory $collectionFactory,
        ResourceModel\Pricing $resourceModel,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_pricingFactory = $pricingFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(PricingInterface $pricing)
    {
        $pricingId = $pricing->getId();
        try {
            $this->_resourceModel->save($pricing);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                $e->getMessage()
            );
        }
        unset($this->_instancesById[$pricing->getId()]);

        return $this->getById($pricing->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getById($pricingId)
    {
        $pricingData = $this->_pricingFactory->create();
        /* @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\Collection $pricingData */
        $pricingData->load($pricingId);
        if (!$pricingData->getId()) {
            // pricing record does not exist
            //throw new NoSuchEntityException(__('Requested pricing record doesn\'t exist'));
        }
        $this->_instancesById[$pricingId] = $pricingData;

        return $this->_instancesById[$pricingId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /**
 * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\Collection $collection
*/
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PricingInterface $pricing)
    {
        $pricingId = $pricing->getId();
        try {
            $this->_resourceModel->delete($pricing);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove pricing record with id %1', $pricingId)
            );
        }
        unset($this->_instancesById[$pricingId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($pricingId)
    {
        $pricing = $this->getById($pricingId);

        return $this->delete($pricing);
    }
}
