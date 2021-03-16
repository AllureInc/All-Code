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
use Webkul\MpAdvertisementManager\Api\Data\BlockInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class BlockRepository implements \Webkul\MpAdvertisementManager\Api\BlockRepositoryInterface
{
    /**
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var Block[]
     */
    protected $_instances = [];

    /**
     * @var Block[]
     */
    protected $_instancesById = [];

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Block
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @param BlockFactory                                         $blockFactory
     * @param ResourceModel\Block\CollectionFactory                $collectionFactory
     * @param ResourceModel\Block                                  $resourceModel
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        BlockFactory $blockFactory,
        ResourceModel\Block\CollectionFactory $collectionFactory,
        ResourceModel\Block $resourceModel,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_blockFactory = $blockFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(BlockInterface $block)
    {
        //$blockId = $block->getId();
        try {
            $this->_resourceModel->save($block);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                $e->getMessage()
            );
        }
        unset($this->_instancesById[$block->getId()]);

        return $this->getById($block->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getById($blockId)
    {
        $blockData = $this->_blockFactory->create();
        /* @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\Collection $blockData */
        $blockData->load($blockId);
        if (!$blockData->getId()) {
            // block record does not exist
            //throw new NoSuchEntityException(__('Requested block record doesn\'t exist'));
        }
        $this->_instancesById[$blockId] = $blockData;

        return $this->_instancesById[$blockId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /**
 * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\Collection $collection
*/
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BlockInterface $block)
    {
        $blockId = $block->getId();
        try {
            $this->_resourceModel->delete($block);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove block record with id %1', $blockId)
            );
        }
        unset($this->_instancesById[$blockId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($blockId)
    {
        $block = $this->getById($blockId);

        return $this->delete($block);
    }
}
