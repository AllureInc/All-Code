<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Block\Users;

use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\Seller;
use Webkul\MpPushNotification\Model\UsersTokenFactory;
use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;

/**
 * Webkul MpPushNotification Users UsersList Block
 */
class UsersList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpPushNotification\Helper\Data
     */
    protected $_dataHelper;

    /*
    UsersToken
     */
    protected $_usersToken;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Webkul\MpPushNotification\Helper\Data           $dataHelper
     * @param UsersTokenFactory                                $usersToken
     * @param TemplatesRepositoryInterface                     $templatesRepository
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpPushNotification\Helper\Data $dataHelper,
        UsersTokenFactory $usersToken,
        TemplatesRepositoryInterface $templatesRepository,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_usersToken = $usersToken;
        $this->_templatesRepository = $templatesRepository;
        parent::__construct($context, $data);
        $this->setCollection($this->getRegisteredUsersList());
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
    public function getRegisteredUsersList()
    {
        $filters = $this->getParams();
        $tokens = $this->_usersToken->create()->getCollection();
        $filterDateTo = null;
        $filterDateFrom = null;
        $to = null;
        $from = null;
        if (isset($filters['browser']) && $filters['browser']!='') {
            $tokens->addFieldToFilter(
                'browser',
                [
                    'eq'=>trim($filters['browser'])
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
            $tokens->getSelect()->where(
                "main_table.created_at BETWEEN '".$from."' AND '".$to."'"
            );
        }

        $tokens->setOrder('entity_id', 'DESC');
        return $tokens;
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
                'mp.MpPushNotification.users.list.pager'
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
     * get seller templates list
     * @return [type] [description]
     */
    public function getSellerTemplatesList()
    {
        $sellerId = $this->_customerSession->getCustomer()->getId();
        return $this->_templatesRepository->getBySellerId($sellerId);
    }
}
