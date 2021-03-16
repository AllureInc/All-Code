<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 * Block Class
 * For rendering tag manager JS
 */
namespace Cnnb\GtmWeb\Block;

/**
 * DataLayer | Block Class
 */
class DataLayer extends DataLayerAbstract
{
    /**
     * Render tag manager script if GTM (360) & GTM (WEB) both are active
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_gtmHelper->isEnabled() && !$this->_gtmHelper->getCurrentModuleStatus()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Get GTM web status
     *
     * @return string
     */
    public function getCurrentModuleStatus()
    {
        return $this->_gtmHelper->getCurrentModuleStatus();
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
     * Get Mega Menu Mapping Data
     *
     * @return Array
     */
    public function getMegaMenuMappingData()
    {
        return $this->_gtmHelper->getMegaMenuMappingData();
    }

    /**
     * Get Mega Menu Mapped Classes
     *
     * @return Array
     */
    public function getMegaMenuClasses()
    {
        return $this->_gtmHelper->getMegaMenuClasses();
    }

    /**
     * Get Mega Menu Ecommerce Value
     *
     * @return String
     */
    public function getMegaMenuEcommerceValue()
    {
        return $this->_gtmHelper->getMegaMenuEcommerceValue();
    }

    /**
     * Get Footer Link Mapping
     *
     * @return Array
     */
    public function getFooterLinkMapping()
    {
        return $this->_gtmHelper->getFooterLinkMapping();
    }

    /**
     * Get Footer Mapped Classes
     *
     * @return Array
     */
    public function getFooterClasesAndIds()
    {
        return $this->_gtmHelper->getFooterClasesAndIds();
    }

    /**
     * Get Related Article Mapping
     *
     * @return Array
     */
    public function getRelatedArticlesMapping()
    {
        return $this->_gtmHelper->getRelatedArticlesMapping();
    }

    /**
     * Get Related Article Mapped Classes
     *
     * @return Array
     */
    public function getRelatedArticleClasesAndIds()
    {
        return $this->_gtmHelper->getRelatedArticleClasesAndIds();
    }

    /**
     * Get Country Selector's Configurations
     * @return array
     */
    public function getCountrySelectorsConfig()
    {
        return $this->_gtmHelper->getCountrySelectorsConfig();
    }

    /**
     * Get Language Selector's Configurations
     * @return array
     */
    public function getLanguageSelectorsConfig()
    {
        return $this->_gtmHelper->getLanguageSelectorsConfig();
    }

    /**
     * Get PSP Filter Data
     * @return array
     */
    public function getPspFilterData()
    {
        return $this->_gtmHelper->getPspFilterData();
    }

    /**
     * Get PSP CTA Mapping Configuration
     * @return array
     */
    public function getPspCtaMapping()
    {
        return $this->_gtmHelper->getPspCtaMapping();
    }

    /**
     * Get PSP CTA Classes And Ids
     * @return array
     */
    public function getPspCtaClasesAndIds()
    {
        return $this->_gtmHelper->getPspCtaClasesAndIds();
    }

    /**
     * Get PSP Other Services Mapping
     * @return array
     */
    public function getPspOtherServicesMapping()
    {
        return $this->_gtmHelper->getPspOtherServicesMapping();
    }

    /**
     * Get PSP Other Service Classes and Ids
     * @return array
     */
    public function getOtherServiceClassesAndIds()
    {
        return $this->_gtmHelper->getOtherServiceClassesAndIds();
    }

    /**
     * Get Category Products Data
     * @return array
     */
    public function getCurrentCategoryProducts()
    {
        return $this->_gtmHelper->getCurrentCategoryProducts();
    }

    /**
     * Get Rating Mapping Configuration
     * @return array
     */
    public function getRatingMappingConfiguration()
    {
        return $this->_gtmHelper->getRatingMappingConfiguration();
    }

    /**
     * Get Rating Classes and Ids
     * @return array
     */
    public function getRatingClassesAndIds()
    {
        return $this->_gtmHelper->getRatingClassesAndIds();
    }

    /**
     * Get Back to Top Configuration
     * @return array
     */
    public function getBackToTopConfiguration()
    {
        return $this->_gtmHelper->getBackToTopConfiguration();
    }

    /**
     * Get CMS Page URI
     * @return array
     */
    public function getCmsPageUri()
    {
        return $this->_gtmHelper->getCmsPageUri();
    }

    /**
     * get Kprofile Send Email Button Selector
     * @return array
     */
    public function getKprofileSendEmailButtonSelector()
    {
        return $this->_gtmHelper->getKprofileSendEmailButtonSelector();
    }

    /**
     * Get Diagnostic Send By Email Mapping
     * @return array
     */
    public function getDiagnosticSendByEmailMapping()
    {
        return $this->_gtmHelper->getDiagnosticSendByEmailMapping();
    }

    /**
     * Get Diagnostic Send By Email Elements
     * @return array
     */
    public function getDiagnosticSendByEmailElements()
    {
        return $this->_gtmHelper->getDiagnosticSendByEmailElements();
    }

    /**
     * get Diagnostic Send By Email Submit Mapping
     * @return array
     */
    public function getDiagnosticSendByEmailSubmitMapping()
    {
        return $this->_gtmHelper->getDiagnosticSendByEmailSubmitMapping();
    }

    /**
     * get Diagnostic Send By Email Submit Elements
     * @return array
     */
    public function getDiagnosticSendByEmailSubmitElements()
    {
        return $this->_gtmHelper->getDiagnosticSendByEmailSubmitElements();
    }

    /**
     * get Diagnose Shop Now Cta
     * @return array
     */
    public function getDiagnoseShopNowCta()
    {
        return $this->_gtmHelper->getDiagnoseShopNowCta();
    }

    /**
     * Get Diagnose Shop Now Cta Elements
     * @return array
     */
    public function getDiagnoseShopNowCtaElements()
    {
        return $this->_gtmHelper->getDiagnoseShopNowCtaElements();
    }

    /**
     * Get Daignose Newsletter Data
     * @return array
     */
    public function getDaignoseNewsletterData()
    {
        return $this->_gtmHelper->getDaignoseNewsletterData();
    }

    /**
     * Get Daignose CGU Data
     * @return array
     */
    public function getDaignoseCguData()
    {
        return $this->_gtmHelper->getDaignoseCguData();
    }

    /**
     * Get wish list mapping config
     * @return array
     */
    public function getWishListClassIdsAndProduct()
    {
        return $this->_gtmHelper->getWishListClassIdsAndProduct();
    }

    /**
     * Get news letter mapping config
     * @return array
     */
    public function getNewsletterConfig()
    {
        return $this->_gtmHelper->getNewsletterConfig();
    }

    /**
     * Get news letter classes and ids
     * @return array
     */
    public function getNewsletterClassId()
    {
        return $this->_gtmHelper->getNewsletterClassId();
    }

    /**
     * Get chat with us config
     * @return array
     */
    public function getChatwithusConfig()
    {
        return $this->_gtmHelper->getChatwithusConfig();
    }

    /**
     * Get chat with us classes and ids
     * @return array
     */
    public function getChatwithusClassId()
    {
        return $this->_gtmHelper->getChatwithusClassId();
    }

    /**
     * Get diagnosis config data
     * @return array
     */
    public function getDiagnosisData()
    {
        return $this->_gtmHelper->getDiagnosisData();
    }

    /**
     * Get pdp configuration
     * @return array
     */
    public function getPdpConfiguration()
    {
        return $this->_gtmHelper->getPdpConfiguration();
    }

    /**
     * Get pdp classes and ids
     * @return array
     */
    public function getpdpClassesAndIds()
    {
        return $this->_gtmHelper->getpdpClassesAndIds();
    }

    /**
     * Get pdp tab configuration
     * @return array
     */
    public function getPdpTabConfiguration()
    {
        return $this->_gtmHelper->getPdpTabConfiguration();
    }

    /**
     * Get pdp tab classes and ids
     * @return array
     */
    public function getPdpTabClassesAndIds()
    {
        return $this->_gtmHelper->getPdpTabClassesAndIds();
    }

    /**
     * Get pdp arrow configuration
     * @return array
     */
    public function getPdpArrowConfiguration()
    {
        return $this->_gtmHelper->getPdpArrowConfiguration();
    }

    /**
     * Get pdp arrow classes and ids
     * @return array
     */
    public function getPdpArrowClassesAndIds()
    {
        return $this->_gtmHelper->getPdpArrowClassesAndIds();
    }

    /**
     * Get product name for product detail page
     * @return array
     */
    public function getNameFromDetailPage()
    {
        return $this->_gtmHelper->getNameFromDetailPage();
    }

    /**
     * Get product name and id for category page
     * @return array
     */
    public function getProductNameAndId()
    {
        return $this->_gtmHelper->getProductNameAndId();
    }
    
    /**
     * Get saloon configuration
     * @return array
     */
    public function getSaloonConfiguration()
    {
        return $this->_gtmHelper->getSaloonConfiguration();
    }

    /**
     * Get saloon classes and ids
     * @return array
     */
    public function getSaloonClassesAndIds()
    {
        return $this->_gtmHelper->getSaloonClassesAndIds();
    }

    /**
     * Get customer status
     * @return array
     */
    public function getCustomerStatus()
    {
        return $this->_gtmHelper->getCustomerStatus();
    }
}
