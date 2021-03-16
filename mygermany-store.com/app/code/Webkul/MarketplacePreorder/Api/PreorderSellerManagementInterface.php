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
namespace Webkul\MarketplacePreorder\Api;

interface PreorderSellerManagementInterface
{
    /**
     * [saveConfig description]
     * @param  \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $sellerData
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveConfig(\Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $sellerData);

    /**
     * [saveConfig description]
     * @param  \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $sellerData
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validation($sellerData);
}
