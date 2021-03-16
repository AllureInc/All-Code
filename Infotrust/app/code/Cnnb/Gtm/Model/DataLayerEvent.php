<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Model Class
 * For declaring the events
 */
namespace Cnnb\Gtm\Model;

/**
 * DataLayerEvent Model Class
 */
class DataLayerEvent
{
    const PRODUCT_PAGE_EVENT = 'productPage';
    const CATEGORY_PAGE_EVENT = 'categoryPage';
    const CHECKOUT_PAGE_EVENT = 'checkoutPage';
    const ORDER_SUCCESS_PAGE_EVENT = 'orderSuccessPage';
    const ORDER_PAGE_EVENT = 'orderPage';
}
