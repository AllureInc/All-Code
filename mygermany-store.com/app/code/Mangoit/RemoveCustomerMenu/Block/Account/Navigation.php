<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\RemoveCustomerMenu\Block\Account;

use \Magento\Framework\View\Element\Html\Links;
use \Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Class for sorting links in navigation panels.
 *
 * @api
 * @since 100.2.0
 */
class Navigation extends \Magento\Customer\Block\Account\Navigation
{
    /**
     * {@inheritdoc}
     * @since 100.2.0
     */
    public function getLinks()
    {
        $removeMenu = ['account dashboard', 'my orders', 'my downloadable products', 'my wish list', 'address book', 'account information', 'stored payment methods', 'billing agreements', 'my product reviews', 'newsletter subscriptions', 'my tickets'];
        $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
        $webkulHelper = $objectManager->create('Webkul\Marketplace\Helper\Data');
        $isSeller = $webkulHelper->isSeller();
        $sellerData = $webkulHelper->getSellerData()->getFirstItem()->getBecomeSellerRequest();
        $links = $this->_layout->getChildBlocks($this->getNameInLayout());
        $sortableLink = [];
        foreach ($links as $key => $link) {
            if ($this->getNameInLayout() == 'customer_account_navigation') {
                // print_r($isSeller);
                // print_r($sellerData);
                // die('died');
                if ($isSeller || ($sellerData)) {
                // if ((!$isSeller) && ($seller_exist)) {
                    unset($links[$key]);
                    continue;
                }
                if($isSeller) {
                    if(in_array(strtolower($link['label']), $removeMenu)) {
                        unset($links[$key]);
                    }
                } /*else*/
            }

            if ($link instanceof SortLinkInterface) {
                $sortableLink[] = $link;
                unset($links[$key]);
            }
        }

        usort($sortableLink, [$this, "compare"]);
        return array_merge($sortableLink, $links);
    }

    /**
     * Compare sortOrder in links.
     *
     * @param SortLinkInterface $firstLink
     * @param SortLinkInterface $secondLink
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function compare(SortLinkInterface $firstLink, SortLinkInterface $secondLink)
    {
        return ($firstLink->getSortOrder() < $secondLink->getSortOrder());
    }
}
