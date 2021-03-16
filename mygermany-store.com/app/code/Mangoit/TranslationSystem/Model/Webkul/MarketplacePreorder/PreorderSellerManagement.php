<?php

namespace Mangoit\TranslationSystem\Model\Webkul\MarketplacePreorder;

use Webkul\MarketplacePreorder\Api\PreorderSellerManagementInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProduct;
use Webkul\MarketplacePreorder\Api\PreorderSellerRepositoryInterface;
use Webkul\MarketplacePreorder\Api\Data\PreorderSellerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

use Magento\Store\Model\StoreManagerInterface;

class PreorderSellerManagement extends \Webkul\MarketplacePreorder\Model\PreorderSellerManagement
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        MarketplaceProduct $marketplaceProduct,
        \Magento\Customer\Model\Session $customerSession,
        PreorderSellerRepositoryInterface $sellerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;

        parent::__construct(
            $marketplaceHelper,
            $productFactory,
            $marketplaceProduct,
            $customerSession,
            $sellerRepository,
            $searchCriteriaBuilder,
            $date
        );
    }

    /**
     *
     * @param  \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $sellerData [
     * @return PreorderSellerInterface
     */
    public function saveConfig(\Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $sellerData)
    {
        /**
         * Validate input data.
         */
        $this->validation($sellerData);
        if (!count($this->_error)) {
            $sellerData->setSellerId($this->getCustomerId());
            $sellerData->setTime($this->_date->gmtDate());

            $customMessages = [];

            $custom_message = $sellerData->getCustomMessage();
            if (!$custom_message) {
                $custom_message = 'preorder this product and we will soon get back to you';
            }
            $customMessages[0] = $custom_message;

            // $storeManagerDataList = $this->storeManager->getStores();
            // foreach ($storeManagerDataList as $key => $value) {
            //     $customMessages[$value->getCode()] = $custom_message;
            // }
             
            // if (!$sellerData->getCustomMessage()) {
            //     $sellerData->setCustomMessage('preorder this product and we will soon get back to you');
            // }

            $searchCriteria = $this->_sellerSearchInterface->addFilter(
                'seller_id',
                $this->getCustomerId(),
                'eq'
            )->create();
            $items = $this->_sellerRepository->getList($searchCriteria);

            $entityId = 0;
            foreach ($items->getItems() as $value) {
                $entityId = $value['id'];
            }
            if ($entityId) {

                $savedCustomMsgs = $this->_sellerRepository->getById($entityId)->getCustomMessage();
                if($savedCustomMsgs) {
                    $customMessages = unserialize($savedCustomMsgs);
                }
                $sellerData->setId($entityId);
            }

            $customMessages[$this->storeManager->getStore()->getCode()] = $custom_message;
            $sellerData->setCustomMessage(serialize($customMessages));

            try {
                $this->_sellerRepository->save($sellerData);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        } else {
            foreach ($this->_error as $value) {
                throw new \Magento\Framework\Exception\LocalizedException($value);
                break;
            }
        }
    }
}
