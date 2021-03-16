<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Model;

use Webkul\MarketplacePreorder\Api\PreorderSellerManagementInterface;
use Webkul\Marketplace\Model\ProductFactory as MarketplaceProduct;
use Webkul\MarketplacePreorder\Api\PreorderSellerRepositoryInterface;
use Webkul\MarketplacePreorder\Api\Data\PreorderSellerSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Marketplace Preorder.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreorderSellerManagement implements PreorderSellerManagementInterface
{
    const NO_SKU = 0;
    const NO_PRODUCT = 1;
    const NO_SELLER = 2;
    const NO_MP_PRODUCT = 3;

    protected $_error = [];

    /**
     * @var \Webkul\Marketplace\Helper
     */
    protected $_marketplaceHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var MarketplaceProduct
     */
    protected $_marketplaceProduct;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var PreorderSellerRepositoryInterface
     */
    protected $_sellerRepository;

    /**
     * @var  SearchCriteriaBuilder
     */
    protected $_sellerSearchInterface;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    public function __construct(
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        MarketplaceProduct $marketplaceProduct,
        \Magento\Customer\Model\Session $customerSession,
        PreorderSellerRepositoryInterface $sellerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_productFactory = $productFactory;
        $this->_marketplaceProduct = $marketplaceProduct;
        $this->_customerSession = $customerSession;
        $this->_sellerRepository = $sellerRepository;
        $this->_sellerSearchInterface = $searchCriteriaBuilder;
        $this->_date = $date;
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

            if (!$sellerData->getCustomMessage()) {
                $sellerData->setCustomMessage('preorder this product and we will soon get back to you');
            }
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
                $sellerData->setId($entityId);
            }
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
    /**
     * Validate Seller Prorder Configuration
     * @param  array $sellerData
     * @return array
     */
    public function validation($sellerData)
    {
        $data = $sellerData->getData();

        foreach ($data as $code => $value) {

            switch ($code) {
                case 'type':
                    if ($value == '' || $value == null) {
                        $this->_error[] = __('Please select valid preorder type from the list');
                    }
                    break;

                case 'preorder_percent':
                    if ($value == '' || (int)$value < 1 || (int)$value > 100) {
                        $this->_error[] = __($value.' is not a valid percent number');
                    }
                    break;

                case 'few_products':
                    if ($value == 0 && $data['preorder_action'] == 2) {
                        $this->_error[] = __('Please provide the products for "few products for preorder"');
                    } elseif ($data['preorder_action'] == 2) {
                        $result = $this->validateSku($value);
                        if ($result == self::NO_PRODUCT) {
                            $this->_error[] = __(
                                'Please provide the valid product SKUs, as %s are all invalid produc SKUs',
                                implode(',', $value)
                            );
                        }
                        if ($result == self::NO_SKU) {
                            $this->_error[] = __(
                                'Please provide the product SKUs as comma(,)
                                seperated for "all products except some products for preorder"'
                            );
                        }
                        if ($result == self::NO_SELLER) {
                            $this->_error[] = __(
                                'You are not a seller'
                            );
                        }
                    }
                    break;
                case 'disable_products':
                    if(trim($value))
                    {
                        if (count($value) == 0 && $data['preorder_action'] == 2) {
                            $this->_error[] = __('Please provide the products for "few products for preorder"');
                        } elseif ($data['preorder_action'] == 2) {
                            $result = $this->validateSku($value);
                            if(is_array($value))
                            $wkvalue = implode(',', $value);
                            else
                                $wkvalue = $value;
                            if ($result == self::NO_PRODUCT) {
                                $this->_error[] = __(
                                    'Please provide the valid product SKUs, as %s are all invalid produc SKUs',
                                    $wkvalue
                                );
                            }
                            if ($result == self::NO_SKU) {
                                $this->_error[] = __(
                                    'Please provide the product SKUs as comma(,)
                                    seperated for "all products except some products for preorder"'
                                );
                            }
                            if ($result == self::NO_SELLER) {
                                $this->_error[] = __(
                                    'You are not a seller'
                                );
                            }
                        }
                    }
                    break;
                case 'email_type':
                    if ($value == '' || $value == null) {
                        $this->_error[] = __('Please select the valid option to send email to customer');
                    }
                    break;
                case 'mppreorder_qty':
                    if ($value == '' || $value == null) {
                        $this->_error[] = __('Please select the valid option of preorder quantity');
                    }
                    break;
                case 'mppreorder_specific':
                    if ($value == '' || $value == null) {
                        $this->_error[] = __('Please select the valid option of buyer specification');
                    }
                    break;
                default:
                    break;
            }
        }
        return $this->_error;
    }

    /**
    * This function is used to get the seller id of seller
    * @return integer
    **/
    public function getCustomerId()
    {
        return $this->_marketplaceHelper->getCustomerId();
    }

    /**
     * Validate Product SKU
     * @param  string|null $skus
     * @return CONST
     */
    public function validateSku($skus)
    {

        $customerId = $this->getCustomerId();

        if(!is_array($skus))
            $skus = explode(',',$skus);

        if (count($skus)) {
            $productIds = [];
            $marketplaceProductIds = [];
            foreach ($skus as $sku) {
                $productId = $this->_productFactory->create()->getIdBySku($sku);
                $productIds[$sku] = $productId;
            }

            if (count($productIds) <= 0) {
                return self::NO_PRODUCT;
            }
            $isSeller = $this->_marketplaceHelper->isSeller();
            if ($isSeller) {
                $products = $this->_marketplaceProduct->create()
                    ->getCollection()
                    ->addFieldToFilter('seller_id', ['eq' => $customerId]);

                $markeplaceProductIds = [];
                foreach ($products as $product) {
                    $marketplaceProductIds[] = $product->getMageproductId();
                }
                $finalIds = array_intersect($productIds, $marketplaceProductIds);
                if (count($skus) != count($finalIds)) {
                    return self::NO_PRODUCT;
                }
                return array_intersect($productIds, $marketplaceProductIds);
            } else {
                return self::NO_SELLER;
            }
        } else {
            return self::NO_SKU;
        }
    }
}
