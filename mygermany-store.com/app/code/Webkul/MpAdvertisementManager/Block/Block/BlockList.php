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
namespace Webkul\MpAdvertisementManager\Block\Block;

class BlockList extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_adsHelper;

    /**
     * @var \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\CollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;


    /**
     * __construct
     *
     * @param \Magento\Framework\View\Element\Template\Context                           $context
     * @param \Webkul\MpAdvertisementManager\Helper\Data                                 $adsHelper
     * @param \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     * @param \Webkul\Marketplace\Helper\Data                                            $mpHelper
     * @param array                                                                      $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpAdvertisementManager\Helper\Data $adsHelper,
        \Webkul\MpAdvertisementManager\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
    
        $this->_adsHelper = $adsHelper;
        $this->_blockCollectionFactory = $blockCollectionFactory;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context, $data);

        $collection = $this->_blockCollectionFactory->create()->addFieldToFilter(
            'seller_id',
            ['eq'=>$this->_mpHelper->getCustomerId()]
        );

        $filter = $this->getRequest()->getParam('title');

        if ($filter) {
            $collection->getSelect()->where("title LIKE '%".$filter."%'");
        }

        $this->setCollection($collection);
    }

    /**
     * _prepareLayout prepare pager for rules list
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'adblocks.list.pager'
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
}
