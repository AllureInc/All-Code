<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store\Renderer;

/**
 * Class Magento
 * @package Plenty\Core\Block\Adminhtml\Profile\Config\Form\Field\Store\Renderer
 */
class Magento extends \Magento\Framework\View\Element\Html\Select
{
    private $_stores;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     */
    protected $_addGroupAllOption = true;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        array $data = [])
    {
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getStores() as $storeCode => $storeLabel) {
                $this->addOption($storeCode, addslashes($storeLabel));
            }
        }
        return parent::_toHtml();
    }

    private function _getStores()
    {
        if (null === $this->_stores) {
            $this->_stores = [];
            foreach ($this->storeRepository->getList() as $store) {
                $website = $this->websiteRepository->getById($store->getWebsiteId());
                $this->_stores[$store->getCode()] = "{$store->getName()} ({$website->getName()})";
            }
        }

        return $this->_stores;
    }
}
