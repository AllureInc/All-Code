<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;

class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    const PATH = 'marketplace/mppushnotification/';

    /**
     * object of store manger class
     * @var storemanager
     */
    protected $_storeManager;

    /**
     * object of TemplatesRepositoryInterface
     * @var badgeRepository
     */
    protected $_templatesRepo;

    /**
     * [__construct description]
     * @param ContextInterface             $context
     * @param UiComponentFactory           $uiComponentFactory
     * @param StoreManagerInterface        $storemanager
     * @param array                        $components
     * @param array                        $data
     * @param TemplatesRepositoryInterface $templatesRepo
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storemanager,
        TemplatesRepositoryInterface $templatesRepo,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_storeManager = $storemanager;
        $this->_templatesRepo = $templatesRepo;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $template = $this->_templatesRepo->getById($item['entity_id']);
                $imageName = $template->getLogoUrl();
                $imageTitle = $template->getTitle();
                $item[$fieldName . '_src'] = $imageName;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $imageTitle;
                $item[$fieldName . '_orig_src'] = $imageName;
            }
        }
        return $dataSource;
    }
}
