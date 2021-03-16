<?php
namespace Cnnb\GoogleReviewSnippet\Plugin;

use Magento\Catalog\Helper\Product;
use Cnnb\GoogleReviewSnippet\Helper\Data as HelperData;
use Cnnb\GoogleReviewSnippet\Logger\Logger;

class CanUseCanonicalTagForProducts
{
    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var HelperData
     */
    protected $_logger;

    /**
     * CanUseCanonicalTagForProducts constructor.
     *
     * @param HelperData $helper
     */
    function __construct(
        HelperData $helper,
        Logger $logger
    )
    {
        $this->_helper = $helper;
        $this->_logger = $logger;
    }

    /**
     * @param Product $product
     * @param $result
     *
     * @return mixed
     */
    public function afterCanUseCanonicalTag(Product $product, $result)
    {
        $this->_logger->info("=======  After Can Use Canonical Tag =====");
        if ($this->_helper->isGoogleReviewModuleEnabled()) {
            return true;
        }

        $this->_logger->info("=======  After Can Use Canonical Tag =====");
        return true;
    }
}
