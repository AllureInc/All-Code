<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Block\Templates;

use Webkul\MpPushNotification\Api\TemplatesRepositoryInterface;

/**
 * Webkul MpPushNotification Templates Edit Block
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    const PATH = 'marketplace/mppushnotification/';

    /*
    TemplatesRepositoryInterface
     */
    protected $_templatesRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $_storemanager;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param TemplatesRepositoryInterface                     $templatesRepository
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        TemplatesRepositoryInterface $templatesRepository,
        array $data = []
    ) {
        $this->_templatesRepository = $templatesRepository;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * get parameters
     * @return array
     */
    public function getParams()
    {
        $filters = $this->getRequest()->getParams();
        return $filters;
    }

    /**
     * get template dtaa by id
     * @return array
     */
    public function getTemplateData()
    {
        $params = $this->getParams();
        $collection = $this->_templatesRepository->getById($params['id']);
        return $collection->getData();
    }

    /**
     * get full image url
     * @param  string $image
     * @return string
     */
    public function getImageView($image)
    {
        $mediaDirectory = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaDirectory.self::PATH.$image;
    }
}
