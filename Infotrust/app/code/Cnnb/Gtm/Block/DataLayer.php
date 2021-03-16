<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For rendering tag manager JS
 */
namespace Cnnb\Gtm\Block;

/**
 * DataLayer | Block Class
 */
class DataLayer extends DataLayerAbstract
{
    /**
     * Render tag manager script
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_gtmHelper->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get Account Id
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->_gtmHelper->getAccountId();
    }

    /**
     * @param null $store_id
     * @return int
     */
    public function addJsInHead($store_id = null)
    {
        return (int) $this->_gtmHelper->addJsInHead($store_id);
    }
}
