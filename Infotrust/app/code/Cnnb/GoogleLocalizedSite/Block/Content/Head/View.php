<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GoogleLocalizedSite
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 */
namespace Cnnb\GoogleLocalizedSite\Block\Content\Head;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * Cnnb\GoogleLocalizedSite\Helper\Data
     */
    protected $_helper;
    
    /**
     * Magento\Framework\App\Request\Http
     */
    protected $_request;
    
    /**
     * Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Cnnb\GoogleLocalizedSite\Helper\Data
     * Magento\Framework\App\Request\Http
     * Magento\Framework\View\Element\Template\Context
     */
    public function __construct(
        \Cnnb\GoogleLocalizedSite\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Function for generating the link
     * @return  string
     */
    public function getLinkData()
    {
        $hrefEnabled = $this->_helper->isHrefLangEnabled();
        $addLinks = $this->addLinkForCurrentPage();
        $isEmpty = empty($this->_helper->getStoreUrls());
        $run_for_all_stores = true;

        $this->_logger->info("=========== getLinkData ========");

        if ($hrefEnabled && $addLinks && !$isEmpty) {
            $current_url = $this->getSiteUrlWithStoreCode();
            $original_current_url = $this->_helper->getOriginalCurrentUrl();
            $link = '';
            $selected_language_array = [];
            $cur_lang = $this->_helper->getCurrentLanguageCode();
            $other_langs = $this->_helper->getLocaleOfAllStores();
            try {
                if ($run_for_all_stores) {
                    $link .= $other_langs;

                    foreach ($this->_helper->getStoreUrls() as $store_id => $url) {
                        if (!empty($this->_helper->getCountryLangMapping($store_id))) {
                            $county_lang_mapping = $this->_helper->getCountryLangMapping($store_id);
                            foreach ($county_lang_mapping as $key => $value) {
                                $lang = explode('_', $value['cnnb_language'])[0];
                                $country = $value['cnnb_country'];
                                $link .= '<link rel="alternate" href="'.$url.'" hreflang="'.$lang.'-'.$country.'" />';
                            }
                        }
                    }
                }
                
            } catch (Exception $e) {
                $this->_logger->info(" #### Exception: ".$e->getMessage());
            }

            if ($this->_helper->isEnabledXdefault()) {
                $link .= '<link rel="alternate" href="'.$current_url.'" hreflang="x-default" />';
            }
            
            return $link;
        } else {
            $this->_logger->info(" #### These variables has problems: isHrefLangEnabled, addLinkForCurrentPage, getStoreUrls");
        }
    }

    /**
     * Function for checking the page
     * @return  boolean
     */
    public function addLinkForCurrentPage()
    {
        $pageType = $this->_request->getFullActionName();
        
        switch ($pageType) {
            case 'cms_index_index':
                $result = $this->_helper->isCmsPageEnabled();
                break;

            case 'catalog_category_view':
                $result = $this->_helper->isCategoryEnabled();
                break;

            case 'catalog_product_view':
                $result = $this->_helper->isProductEnabled();
                break;
            
            default:
                $result = false;
                break;
        }

        return $result;
    }

    /**
     * Function for current url
     * @return  boolean
     */
    public function getSiteUrlWithStoreCode()
    {
        $current_url = $this->_helper->getCurrentUrl();
        return $current_url;
    }
}
