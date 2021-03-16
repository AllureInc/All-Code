<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 */
namespace Mangoit\Marketplace\Controller\Catalog;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Webkul\Marketplace\Controller\Catalog\View
{
    /**
     * Product view action
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($this->isAdminloggedIn()) {
            // Get initial data from request
            $categoryId = (int) $this->getRequest()->getParam('category', false);
            $productId = (int) $this->getRequest()->getParam('id');
            $specifyOptions = $this->getRequest()->getParam('options');

            // Prepare helper and params
            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId($categoryId);
            $params->setSpecifyOptions($specifyOptions);

            // Render page
            try {
                $page = $this->resultPageFactory->create();
                $this->viewHelper = $this->_objectManager->create("Webkul\Marketplace\Helper\Product\View");
                $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
                return $page;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return $this->noProductRedirect();
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } else if($this->isVendorlogin()) {
            $categoryId = (int) $this->getRequest()->getParam('category', false);
            $productId = (int) $this->getRequest()->getParam('id');
            $specifyOptions = $this->getRequest()->getParam('options');
            // Prepare helper and params
            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId($categoryId);
            $params->setSpecifyOptions($specifyOptions);

            // Render page
            try {
                $page = $this->resultPageFactory->create();
                $this->viewHelper = $this->_objectManager->create("Webkul\Marketplace\Helper\Product\View");
                $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
                return $page;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return $this->noProductRedirect();
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }

    /**
     * check if Vendor logged in or not
     * @return boolean
     */
    public function isVendorlogin()
    {
        $productId = (int) $this->getRequest()->getParam('id');
        $customerSession = $this->_objectManager->create("Magento\Customer\Model\Session");
        if($customerSession->isLoggedIn()) {
            $webkulHelper = $this->_objectManager->create("Webkul\Marketplace\Helper\Data");
            if ($webkulHelper->isSeller() && $webkulHelper->isRightSeller($productId)) {
                return true;   
            } else {
                return false;
            }
        } else {
            return false;
        }


    }

    /**
     * check if admin logged in or not
     * @return boolean
     */
    public function isAdminloggedIn()
    {
        $sessionId = $this->getRequest()->getParam('SID');
        $dateTime = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $adminConfig = $this->_objectManager->create('Magento\Security\Model\Config');
        $lifetime = $adminConfig->getAdminSessionLifetime();
        $currentTime = $dateTime->gmtTimestamp();
        $adminSession = $this->_objectManager->create('Magento\Security\Model\AdminSessionsManager');
        $lastUpdatedTime = $dateTime->gmtTimestamp($adminSession->getCurrentSession()->getUpdatedAt());
        if (!is_numeric($lastUpdatedTime)) {
            $lastUpdatedTime = strtotime($lastUpdatedTime);
        }
        if ($lastUpdatedTime >= ($currentTime - $lifetime) &&
            $adminSession->getCurrentSession()->getStatus() == 1
        ) {
            return true;
        } else {
            return false;
        }
    }
}
