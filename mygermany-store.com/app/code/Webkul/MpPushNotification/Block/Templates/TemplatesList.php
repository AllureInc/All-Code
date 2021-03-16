<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Block\Templates;

use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\Seller;
use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;

/**
 * Webkul MpPushNotification Users TemplatesList Block
 */
class TemplatesList extends \Magento\Framework\View\Element\Template
{
    const PATH = 'marketplace/mppushnotification/';

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_urlinterface;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpPushNotification\Helper\Data
     */
    protected $_dataHelper;

    /*
    TemplatesRepositoryInterface
     */
    protected $_templatesRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Webkul\MpPushNotification\Helper\Data           $dataHelper
     * @param TemplatesRepositoryInterface                     $templatesRepository
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpPushNotification\Helper\Data $dataHelper,
        TemplatesRepositoryInterface $templatesRepository,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_templatesRepository = $templatesRepository;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
        $this->setCollection($this->getTemplatesList());
    }

    /**
     * get parameters
     * @return array
     */
    public function getParams()
    {
        $filters = $this->getRequest()->getParams();
        return $filters;
    }

    /**
     * get seller coupons
     * @return object
     */
    public function getTemplatesList()
    {
        $filters = $this->getParams();
        $sellerId = $this->_customerSession->getCustomer()->getId();
        $templates = $this->_templatesRepository->getBySellerId($sellerId);
        $filterDateTo = null;
        $filterDateFrom = null;
        $to = null;
        $from = null;
        if (isset($filters['title']) && $filters['title']!='') {
            $templates->addFieldToFilter(
                ['title','message'],
                [
                    ['like' => '%'.$filters['title'].'%'],
                    ['like' => '%'.$filters['title'].'%'],
                ]
            );
        }
        if (isset($filters['from_date'])) {
            $filterDateFrom = $filters['from_date'] != '' ? $filters['from_date'] : '';
        }
        if (isset($filters['to_date'])) {
            $filterDateTo = $filters['to_date'] != '' ? $filters['to_date'] : '';
        }
        if ($filterDateTo) {
            $todate = date_create($filterDateTo);
            $to = date_format($todate, 'Y-m-d 23:59:59');
        }
        if (!$to) {
            $to = date('Y-m-d 23:59:59');
        }
        if ($filterDateFrom) {
            $fromdate = date_create($filterDateFrom);
            $from = date_format($fromdate, 'Y-m-d H:i:s');
        }

        if ($from && $to) {
            $templates->getSelect()->where(
                "main_table.created_at BETWEEN '".$from."' AND '".$to."'"
            );
        }

        $templates->setOrder('entity_id', 'DESC');
        return $templates;
    }
    
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mp.MpPushNotification.templates.list.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get full image url
     * @param  string $image
     * @return string
     */
    public function getImageView($image)
    {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaDirectory.self::PATH.$image;
    }
}
