<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplacePreorder\Test\Unit\Model;

use Webkul\MarketplacePreorder\Model\PreorderSellerRepository;
use Magento\Framework\Api\SortOrder;

class PreorderSellerRepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected $repository;

    protected $resource;

    protected $model;

    protected $modelData;

    protected $collection;

    protected $repositoryInterface;

    protected $dataObjectHelper;

    protected $storeManager;

    protected $dataObjectProcessor;

    protected $searchResult;

    /**
     * Initialize repository
     */
    protected function setUp()
    {
        $this->resource = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller'
        )->disableOriginalConstructor()
        ->getMock();
        $this->dataObjectProcessor = $this->getMockBuilder(
            'Magento\Framework\Reflection\DataObjectProcessor'
        )->disableOriginalConstructor()
        ->getMock();

        $modelFactory = $this->getMockBuilder('Webkul\MarketplacePreorder\Model\PreorderSellerFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $modelDataFactory = $this->getMockBuilder('Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $searchResultFactory = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Api\Data\PreorderSellerSearchResultsInterfaceFactory'
        )->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $collectionFactory = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory'
        )
        ->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder('\Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getId')->willReturn(0);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->model = $this->getMockBuilder('Webkul\MarketplacePreorder\Model\PreorderSeller')
            ->disableOriginalConstructor()
            ->getMock();
        $this->modelData = $this->getMockBuilder('Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResult = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Api\Data\PreorderSellerSearchResultsInterface'
        )->disableOriginalConstructor()
            ->getmock();
        $this->collection = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\Collection'
        )->disableOriginalConstructor()
        ->getMock();

        $modelFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->model);
        $modelDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->modelData);
        $searchResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->searchResult);
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);

        $this->dataObjectHelper = $this->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = new PreorderSellerRepository(
            $this->resource,
            $modelFactory,
            $modelDataFactory,
            $collectionFactory,
            $searchResultFactory,
            $this->dataObjectHelper,
            $this->dataObjectProcessor,
            $this->storeManager
        );
    }

    /**
     * @test
     */
    public function testSave()
    {
        $this->resource->expects($this->once())
            ->method('save')
            ->with($this->model)
            ->willReturnSelf();
        $this->assertEquals($this->model, $this->repository->save($this->model));
    }

    /**
     * @test
     */
    public function testGetById()
    {
        $id = '123';
        $this->model->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->resource->expects($this->once())
            ->method('load')
            ->with($this->model, $id)
            ->willReturn($this->model);

        $this->assertEquals($this->model, $this->repository->getById($id));
    }
    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveException()
    {
        $this->resource->expects($this->once())
            ->method('save')
            ->with($this->model)
            ->willThrowException(new \Exception());
        $this->repository->save($this->model);
    }

    /**
     * @test
     */
    public function testDeleteById()
    {
        $id = '123';
        $this->model->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->resource->expects($this->once())
            ->method('load')
            ->with($this->model, $id)
            ->willReturn($this->model);
        $this->resource->expects($this->once())
            ->method('delete')
            ->with($this->model)
            ->willReturnSelf();

        $this->assertTrue($this->repository->deleteById($id));
    }
}
