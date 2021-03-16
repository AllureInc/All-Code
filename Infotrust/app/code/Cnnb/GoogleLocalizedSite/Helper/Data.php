<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GoogleLocalizedSite
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper Class
 * For providing extension configurations
 */
namespace Cnnb\GoogleLocalizedSite\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Store\Block\Switcher;

/**
 * Data Helper Class
 */
class Data extends AbstractHelper
{
    /**
     * Active flag
     */
    const ENABLED_HREFLANG = 'google_localize/general/enabled_hreflang';
    
    /**
     * Active flag
     */
    const ENABLED_PRODUCT = 'google_localize/general/enabled_product';
    
    /**
     * Active flag
     */
    const ENABLED_CARTEGORY = 'google_localize/general/enabled_category';
    
    /**
     * Active flag
     */
    const ENABLED_CMS_PAGE = 'google_localize/general/enabled_cms_page';
    
    /**
     * Active flag
     */
    const SELECTED_COUNTRY_LANG_MAPPING = 'google_localize/general/country_lang_mapping';

    /**
     * Active flag
     */
    const ENABLED_X_DEFAULT = 'google_localize/general/enabled_x_default';

    /**
     * Active flag
     */
    const SELECTED_LANGUAGE_CODE = 'general/locale/code';

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_urlInterface;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeInterface;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_actionUrlBuilder;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeRepository;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_switcher;

    /**
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Locale\Resolver $storeInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        UrlBuilder $actionUrlBuilder,
        Switcher $switcher
    ) {
        $this->_storeInterface = $storeInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_actionUrlBuilder = $actionUrlBuilder;
        $this->_storeRepository = $storeRepository;
        $this->_switcher = $switcher;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * Get website identifier
     *
     * @return string|int|null
     */
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getCurrentLanguageCode()
    {
        return explode('_', $this->_storeInterface->getLocale())[0];
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl(false);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getOriginalCurrentUrl()
    {
        return $this->_urlInterface->getCurrentUrl();
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function getScopeConfigValue($path, $store_id = 0)
    {
        if ($store_id == 0) {
            $store_id = $this->getStoreId();
        }
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store_id);
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function isHrefLangEnabled()
    {
        return (int) $this->getScopeConfigValue(self::ENABLED_HREFLANG);
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function isProductEnabled()
    {
        return (int) $this->getScopeConfigValue(self::ENABLED_PRODUCT);
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function isCategoryEnabled()
    {
        return (int) $this->getScopeConfigValue(self::ENABLED_CARTEGORY);
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function isCmsPageEnabled()
    {
        return (int) $this->getScopeConfigValue(self::ENABLED_CMS_PAGE);
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function getCountryLangMapping($store_id = 0)
    {
        return json_decode($this->getScopeConfigValue(self::SELECTED_COUNTRY_LANG_MAPPING, $store_id), true);
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function getSelectedLanguageCode($store_id)
    {
        return $this->_scopeConfig->getValue(self::SELECTED_LANGUAGE_CODE, ScopeInterface::SCOPE_STORE, $store_id);
    }

    /**
     * Get scope config value
     *
     * @return  mixed
     */
    public function isEnabledXdefault()
    {
        return (int) $this->getScopeConfigValue(self::ENABLED_X_DEFAULT);
    }

    /**
     * Function will return all the other store_view URLs
     *
     * @return  mixed
     */
    public function getStoreUrls()
    {
        $urls = [];
        foreach ($this->_switcher->getStores() as $lang) {
            if ($lang->getId() != $this->getStoreId()) {
                if ($this->getSelectedLanguageCode($lang->getId()) != $this->_storeInterface->getLocale()) {
                    $url = $this->_storeManager->getStore($lang->getId())->getCurrentUrl(false);
                    $urls[$lang->getId()] = $url;
                }

            }
        }

        return $urls;
    }

    /**
     * Function will return all the other store_view URLs
     *
     * @return  mixed
     */
    public function getLocaleOfAllStores()
    {
        $link = '';
        foreach ($this->_switcher->getStores() as $lang) {
            if ($lang->getId() != $this->getStoreId()) {
                if ($this->getSelectedLanguageCode($lang->getId()) != $this->_storeInterface->getLocale()) {
                    $url = $this->_storeManager->getStore($lang->getId())->getCurrentUrl(false);
                    $locale = explode('_', $this->getSelectedLanguageCode($lang->getId()))[0];
                    $link .= '<link rel="alternate" href="'.$url.'" hreflang="'.$locale.'" />';
                }

            }
        }

        return $link;
    }
}
