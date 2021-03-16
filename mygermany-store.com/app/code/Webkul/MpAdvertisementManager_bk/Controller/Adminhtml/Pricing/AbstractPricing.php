<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Controller\Adminhtml\Pricing;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

use Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory;
use Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Webkul MpAdvertisementManager Admin Notification Controller
 */
abstract class AbstractPricing extends \Magento\Backend\App\Action
{

    const CURRENT_PRICING_ID = "current_pricing_id";

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $_resultPage;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Webkul\MpAdvertisementManager\Api\Data\PricingInterfaceFactory
     */
    protected $_pricingDataFactory;

    /**
     * @var Webkul\MpAdvertisementManager\Api\PricingRepositoryInterface
     */
    protected $_pricingRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_adsHelper;

    /**
     * @var \Webkul\MpAdvertisementManager\Model\Pricing\CollectionFactory
     */
    protected $_pricingCollection;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @param Context                                                                      $context
     * @param PageFactory                                                                  $resultPageFactory
     * @param ForwardFactory                                                               $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory                             $resultJsonFactory
     * @param PricingRepositoryInterface                                                   $pricingRepository
     * @param PricingInterfaceFactory                                                      $pricingInterface
     * @param Filesystem                                                                   $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory                             $fileUploaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface                                   $storeManager
     * @param \Magento\Framework\Registry                                                  $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                                  $date
     * @param \Webkul\MpAdvertisementManager\Helper\Data                                   $adsHelper
     * @param \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory $pricingCollection
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface                         $timezoneInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        PricingRepositoryInterface $pricingRepository,
        PricingInterfaceFactory $pricingDataFactory,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper,
        \Webkul\MpAdvertisementManager\Model\ResourceModel\Pricing\CollectionFactory $pricingCollection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_pricingRepository = $pricingRepository;
        $this->_pricingDataFactory = $pricingDataFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_date = $date;
        $this->_adsHelper = $adsHelper;
        $this->_pricingCollection = $pricingCollection;
        $this->_timezoneInterface = $timezoneInterface;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAdvertisementManager::pricing');
    }
}
