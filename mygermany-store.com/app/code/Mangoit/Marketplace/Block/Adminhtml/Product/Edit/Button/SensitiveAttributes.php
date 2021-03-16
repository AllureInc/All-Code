<?php
/**
 *  Mangoit_Marketplace
 */
namespace Mangoit\Marketplace\Block\Adminhtml\Product\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
/**
 * Class SensitiveAttributes
 */
class SensitiveAttributes extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{
      /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;


    protected $request;

    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\App\Request\Http $request
    ) {
        parent::__construct($context, $registry);
        $this->request = $request;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $id = $this->request->getParam('id');
        if ($id) {
            return [
                'label' => __('Show Sensitive Attributes'),
                'class' => 'action-secondary mis_sensitive_attr',
                'id' => $this->getUrl('marketplce/product/showsensitiveattrs/', ['id' => $id]),
                'on_click' => '',
                'sort_order' => 20
            ];
        }
    }
}

