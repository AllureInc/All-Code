<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Result;

use Magento\Framework\App\Action\Context;

class Loadproduct extends \Solrbridge\Search\Controller\Result
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\View $viewHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Solrbridge\Search\Helper\Data $viewHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $viewHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Product view action
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('productid');
        $productDetailUrl = '/';
        try {
            $product = $this->productRepository->getById($productId);
            $productDetailUrl = $product->getUrlModel()->getUrl($product);
        } catch (\Exception $e) {
            $productDetailUrl = '/';
        }
        $this->_redirect($productDetailUrl);
    }
}
