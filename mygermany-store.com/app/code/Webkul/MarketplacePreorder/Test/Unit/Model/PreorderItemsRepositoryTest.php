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

use Webkul\MarketplacePreorder\Model\PreorderItemsRepository;

class PreorderItemsRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PreorderItemsRepository
     */
    protected $itemRepository;

    /**
     * @var \Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItemsRepository
     */
    protected $itemResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Webkul\MarketplacePreorder\Model\PreorderItemsRepository
     */
    protected $preorderItem;

    /**
     * @var \Webkul\MarketplacePreorder\Api\Data\PreorderItemsRepositoryInterface
     */
    protected $itemData;

     /**
     * @var \Webkul\MarketplacePreorder\Api\Data\PreorderItemsSearchResultsInterface
     */
    protected $itemSearchResult;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItemsRepository\Collection
     */
    protected $itemCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Initialize repository
     */
    protected function setUp()
    {
        $this->itemResource = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems'
        )->disableOriginalConstructor()
        ->getMock();

        $this->dataObjectProcessor = $this->getMockBuilder('Magento\Framework\Reflection\DataObjectProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder('\Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())->method('getId')->willReturn(0);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->preorderItem = $this->getMockBuilder('Webkul\MarketplacePreorder\Model\PreorderItems')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemData = $this->getMockBuilder('Webkul\MarketplacePreorder\Api\Data\PreorderItemsRepositoryInterface')
            ->getMock();

        $this->itemSearchResult = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Api\Data\PreorderItemsSearchResultsInterface'
        )->getMock();

        $this->itemCollection = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Model\PreorderItemsRepository\Collection'
        )->disableOriginalConstructor()
        ->setMethods(['addFieldToFilter', 'getSize', 'setCurPage', 'setPageSize', 'load', 'addOrder'])
        ->getMock();

        $preorderItemFactory = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Model\PreorderItemsFactory'
        )->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $preorderItemDataFactory = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory'
        )->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $preorderItemSearchResultFactory = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Api\Data\PreorderItemsSearchResultsInterfaceFactory'
        )->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $itemcollectionFactory = $this->getMockBuilder(
            'Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory'
        )->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $preorderItemFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->preorderItem);

        $preorderItemDataFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->itemData);

        $preorderItemSearchResultFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->itemSearchResult);

        $itemcollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->itemCollection);

        $this->dataObjectHelper = $this->getMockBuilder('Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemRepository = new PreorderItemsRepository(
            $this->itemResource,
            $preorderItemFactory,
            $preorderItemDataFactory,
            $itemcollectionFactory,
            $preorderItemSearchResultFactory,
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
        $this->itemResource->expects($this->once())
            ->method('save')
            ->with($this->preorderItem)
            ->willReturnSelf();
        $this->assertEquals($this->preorderItem, $this->itemRepository->save($this->preorderItem));
    }

    /**
     * @test
     */
    public function testGetById()
    {
        $id = '123';
        $this->preorderItem->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->preorderItem->expects($this->once())
            ->method('load')
            ->willReturn($this->preorderItem);

        $this->assertEquals($this->preorderItem, $this->itemRepository->getById($id));
    }

    /**
     * @test
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveException()
    {
        $this->itemResource->expects($this->once())
            ->method('save')
            ->with($this->preorderItem)
            ->willThrowException(new \Exception());
        $this->itemRepository->save($this->preorderItem);
    }

    /**
     * @test
     */
    public function testDeleteById()
    {
        $id = '123';
        $this->preorderItem->expects($this->once())
            ->method('getId')
            ->willReturn(true);
        $this->preorderItem->expects($this->once())
            ->method('load')
            ->willReturn($this->preorderItem);
        $this->itemResource->expects($this->once())
            ->method('delete')
            ->with($this->preorderItem)
            ->willReturnSelf();

        $this->assertTrue($this->itemRepository->deleteById($id));
    }
}
