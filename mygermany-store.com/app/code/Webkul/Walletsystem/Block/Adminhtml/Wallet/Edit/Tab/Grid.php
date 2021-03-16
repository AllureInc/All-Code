<?php
/**
 * Block\Adminhtml\Wallet\Edit\Tab Grid.php
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block\Adminhtml\Wallet\Edit\Tab;

use \Magento\Customer\Model\CustomerFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customer;

     /**
      * @param \Magento\Backend\Block\Template\Context   $context
      * @param \Magento\Backend\Helper\Data              $backendHelper
      * @param CustomerFactory                           $customerFactory
      * @param array                                     $data
      */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_customer = $customerFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('walletcustomergrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'walletsystem/wallet/grid',
            ['_current' => true]
        );
    }
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_customer->create()->getCollection();
        $walletTransactionTable = $collection->getTable('wk_ws_wallet_record');
        $collection->getSelect()->joinLeft(
            ['walletrecord'=>$collection->getTable('wk_ws_wallet_record')],
            'e.entity_id = walletrecord.customer_id',
            [
                "total_amount"=>"total_amount",
                "remaining_amount"=>"remaining_amount"
            ]
        );
        $collection->addNameToSelect();

        $collection->addFilterToMap("total_amount", "walletrecord.total_amount");
        $collection->addFilterToMap("remaining_amount", "walletrecord.remaining_amount");

        $this->setCollection($collection);
        parent::_prepareCollection();
    }
    protected function _setCollectionOrder($column)
    {
        if ($column->getOrderCallback()) {
            call_user_func($column->getOrderCallback(), $this->getCollection(), $column);

            return $this;
        }

        return parent::_setCollectionOrder($column);
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_customer',
            [
                'type' => 'checkbox',
                'name' => 'in_customer',
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction',
                'filter' => false
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Customer ID'),
                'index' =>  'entity_id',
                'class' =>  'customerid'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Customer Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email ID'),
                'index' =>  'email'
            ]
        );
        $this->addColumn(
            'remaining_amount',
            [
                'header' => __('Remaining Wallet Amount'),
                'index' =>  'remaining_amount',
                'type'  => 'currency',
                'filter_index'  => '`walletrecord`.`remaining_amount`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filter_remaining_amount'],
                'order_callback'            => [$this, 'sort_remaining_amount']
            ]
        );
        $this->addColumn(
            'total_amount',
            [
                'header' => __('Total Wallet Amount'),
                'index' =>  'total_amount',
                'type'  => 'currency',
                'filter_index'  => '`walletrecord`.`total_amount`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filter_total_amount'],
                'order_callback'            => [$this, 'sort_total_amount']
            ]
        );
        $this->addColumn(
            'addamount',
            [
                'header' => __('Adjust Wallet Amount'),
                'index' =>  'addamount',
                'renderer' => 'Webkul\Walletsystem\Block\Adminhtml\Wallet\Renderer\Addamountbutton',
                'sortable' => false,
                'filter'    =>  false
            ]
        );
        return parent::_prepareColumns();
    }
    public function filter_remaining_amount($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('walletrecord.remaining_amount', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    public function sort_remaining_amount($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }
    public function filter_total_amount($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('walletrecord.total_amount', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    public function sort_total_amount($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }
}
