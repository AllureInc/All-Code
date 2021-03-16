<?php
/**
 * Sparsh_SalesEmailAttachments
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SalesEmailAttachments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Data extends AbstractHelper
{
    /**
     * SalesEmailAttachments Module XAL path
     *
     * @var XML_PATH_SALESEMAILATTACHMENTS_MODULE
     */
    const XML_PATH_SALESEMAILATTACHMENTS_MODULE = 'sparsh_sales_email_attachments/';

    /**
     * Get Config Value
     *
     * @param string   $field   Field
     * @param int|null $storeId StoreId
     *
     * @return string
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get General Config
     *
     * @param string   $code    Code
     * @param int|null $storeId StoreId
     *
     * @return string
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(
            self::XML_PATH_SALESEMAILATTACHMENTS_MODULE .'general/'. $code,
            $storeId
        );
    }
}
