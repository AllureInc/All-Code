<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MpMassUpload\Api\Data\ProfileInterface;
use Webkul\MpMassUpload\Model\ResourceModel\Profile\CollectionFactory;
use Webkul\MpMassUpload\Model\ResourceModel\Profile as ResourceModelProfile;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProfileRepository implements \Webkul\MpMassUpload\Api\ProfileRepositoryInterface
{
    /**
     * @var ProfileFactory
     */
    protected $_profileFactory;

    /**
     * @var Profile[]
     */
    protected $_instancesById = [];

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ResourceModelProfile
     */
    protected $_resourceModel;

    /**
     * @param ProfileFactory       $profileFactory
     * @param CollectionFactory    $collectionFactory
     * @param ResourceModelProfile $resourceModel
     */
    public function __construct(
        ProfileFactory $profileFactory,
        CollectionFactory $collectionFactory,
        ResourceModelProfile $resourceModel
    ) {
        $this->_profileFactory = $profileFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceModel = $resourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function get($entityId)
    {
        $profileData = $this->_profileFactory->create();
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\Profile\Collection $profileData */
        $profileData->load($entityId);
        if (!$profileData->getId()) {
            $this->_instancesById[$entityId] = $profileData;
        }
        $this->_instancesById[$entityId] = $profileData;

        return $this->_instancesById[$entityId];
    }

    /**
     * {@inheritdoc}
     */
    public function getBySellerId($sellerId)
    {
        $profileCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('customer_id', $sellerId);
        $profileCollection->load();

        return $profileCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getByProfileName($sellerId, $profileName)
    {
        $profileCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('customer_id', $sellerId)
                ->addFieldToFilter('profile_name', $profileId);
        $profileCollection->load();

        return $profileCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\Profile\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(ProfileInterface $profile)
    {
        $entityId = $profile->getId();
        try {
            $this->_resourceModel->delete($profile);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove temp profile data record with id %1', $entityId)
            );
        }
        unset($this->_instancesById[$entityId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($entityId)
    {
        $profile = $this->get($entityId);

        return $this->delete($profile);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBySellerId($sellerId)
    {
        $profile = $this->getBySellerId($sellerId);

        return $this->delete($profile);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByProfileName($sellerId, $profileName)
    {
        $profile = $this->getByProfileName($sellerId, $profileName);

        return $this->delete($profile);
    }
}
