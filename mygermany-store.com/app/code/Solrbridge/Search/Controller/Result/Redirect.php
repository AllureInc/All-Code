<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Result;

use Magento\Framework\App\Action\Context;
use Solrbridge\Search\Model\Doctype\Product\Handler as DoctypeHandler;

class Redirect extends \Magento\Framework\App\Action\Action
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
        $queryData = $this->getRequest()->getParams();
        foreach ($queryData as $key => $value) {
            if ($key !== 'q') {
                $queryData['fq'][$key][] = $value;
                unset($queryData[$key]);
            }
        }
        $searchResultUrl = $this->helper->getResultUrl($queryData);
        $this->_redirect($searchResultUrl);
    }
}
