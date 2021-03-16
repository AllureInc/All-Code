<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderEnhance
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Overrided Order History Block
 */
namespace Cnnb\OrderEnhance\Block\Adminhtml\Order\View;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Framework\App\ObjectManager;
/**
 * Order history block
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Sales\Block\Adminhtml\Order\View\History
{
    const COMM_TEMPLATE = 'Cnnb_OrderEnhance::order/view/history.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $salesData, $registry, $adminHelper, $data);
        $this->_coreRegistry = $registry;
        $this->_salesData = $salesData;
        $this->adminHelper = $adminHelper;
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('order_history_block').parentNode, '" . $this->getSubmitUrl() . "')";
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => __('Submit Comment'), 'class' => 'action-save action-secondary', 'onclick' => $onclick]
        );
        $this->setChild('submit_button', $button);
        if (!$this->getTemplate()) {
            $this->setTemplate(static::COMM_TEMPLATE);
        }
        return parent::_prepareLayout();
    }

    /**
     * Get stat uses
     *
     * @return array
     */
    public function getStatuses()
    {
        $state = $this->getOrder()->getState();
        $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
        $statusCollectionFactory = ObjectManager::getInstance()->get(CollectionFactory::class);
        $options = $statusCollectionFactory->create()->toOptionArray();

        $status_array = [];
        foreach ($options as $key => $value) {
            $status_array[$value['value']] = $value['label'];
        }

        return (!empty($status_array)) ? $status_array : $statuses;
    }

    /**
     * Check allow to send order comment email
     *
     * @return bool
     */
    public function canSendCommentEmail()
    {
        return $this->_salesData->canSendOrderCommentEmail($this->getOrder()->getStore()->getId())
            && $this->_authorization->isAllowed('Magento_Sales::email');
    }

    /**
     * Retrieve order model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * Check allow to add comment
     *
     * @return bool
     */
    public function canAddComment()
    {
        return $this->_authorization->isAllowed('Magento_Sales::comment') && $this->getOrder()->canComment();
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('sales/*/addComment', ['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * Customer Notification Applicable check method
     *
     * @param  \Magento\Sales\Model\Order\Status\History $history
     * @return bool
     */
    public function isCustomerNotificationNotApplicable(\Magento\Sales\Model\Order\Status\History $history)
    {
        return $history->isCustomerNotificationNotApplicable();
    }

    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->adminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }
}
