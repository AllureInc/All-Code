<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Product;

use Magento\Framework\App\Action\Context;

class Load extends \Solrbridge\Search\Controller\Product
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    
    /**
     * @var \Magento\Catalog\Model\View\Asset\Image $imageAsset
     */
    protected $imageAsset;
    
    /**
     * @var \Magento\Catalog\Model\Product\Media\ConfigInterface $mediaConfig
     */
    protected $mediaConfig;
    
    /**
     * @var \Magento\Catalog\Model\Product\Image\UrlBuilder $urlBuilder
     */
    protected $urlBuilder;
    
    /**
     * @var \Magento\Catalog\Model\View\Asset\ImageFactory $viewAssetImageFactory
     */
    protected $viewAssetImageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        //\Magento\Catalog\Model\View\Asset\Image $imageAsset,
        \Magento\Catalog\Model\Product\Media\ConfigInterface $mediaConfig,
        \Magento\Catalog\Model\Product\Image\UrlBuilder $urlBuilder,
        \Magento\Catalog\Model\View\Asset\ImageFactory $viewAssetImageFactory
        
    ) {
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        //$this->imageAsset = $imageAsset;
        $this->mediaConfig = $mediaConfig;
        $this->urlBuilder = $urlBuilder;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        
        parent::__construct($context);
    }

    /**
     * Product view action
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $productId = $this->getRequest()->getQuery('productid');
        
        $product = $this->productRepository->getById($productId);
        
        $imagewidth = 100;
        $imageheight = 100;
        
        $asset = $this->viewAssetImageFactory->create(
            [
                'miscParams' => [],
                'filePath' => 'm/s/msh07-black_back_1.jpg',
            ]
        );
        
        //echo $asset->getUrl();
        
        //echo $this->imageHelper->init($product, 'product_page_image_small')->setImageFile($product->getFile())->resize($imagewidth, $imageheight)->getUrl();
        
        exit();
    }
}
