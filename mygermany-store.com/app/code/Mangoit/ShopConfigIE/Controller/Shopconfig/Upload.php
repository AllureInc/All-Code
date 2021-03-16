<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\ShopConfigIE\Controller\Shopconfig;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreRepository;

class Upload extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var Rate
     */
    protected $_storeRepository;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Mangoit\ShopConfigIE\Helper\Data $massUploadHelper,
        StoreRepository $storeRepository

    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_massUploadHelper = $massUploadHelper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_storeRepository = $storeRepository;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_massUploadHelper;

        if (!$this->marketplaceHelper->isSeller()) {
            $this->messageManager->addError(__('You entered wrong URL.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/index');
        }

        $validateData = $helper->validateUploadedFiles();
        if ($validateData['error']) {
            $this->messageManager->addError(__($validateData['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/*/import');
        }

        /**/
        try {
            $preparedData = array_combine($validateData['csv_data'][0], $validateData['csv_data'][1]);
            $preparedData['taxvat'] = isset($preparedData['taxvat_number']) ? $preparedData['taxvat_number'] : '';
            $preparedData['website_url'] = isset($preparedData['company_url']) ? $preparedData['company_url'] : '';
            $preparedData['country_pic'] = isset($preparedData['country_code']) ? $preparedData['country_code'] : '';
            $preparedData['background_width'] = isset($preparedData['background_color']) ? $preparedData['background_color'] : '';


            $result['id'] = $this->marketplaceHelper->getCustomerId();
            $uploadZip = $helper->uploadZip($result, $preparedData);

            if ($uploadZip['error']) {
                $this->messageManager->addError(__($uploadZip['msg']));
                return $this->resultRedirectFactory->create()->setPath('*/*/import');
            }

            if(isset($preparedData['taxvat']) && $preparedData['taxvat'] != '') {
                $vatNumber = preg_replace("/^[a-z]{2}/i", "", $preparedData['taxvat']); 
                $varValid = $this->_objectManager->get(\Magento\Customer\Model\Vat::class)
                    ->checkVatNumber(
                        $preparedData['country_pic'],
                        $vatNumber
                    );

                if (!$varValid->getIsValid()) {
                    $this->messageManager->addError(__($varValid->getRequestMessage()));
                    return $this->resultRedirectFactory->create()->setPath('*/*/import');
                }
            }

            $sellerId = $this->marketplaceHelper->getCustomerId();
            $stores = $this->_storeRepository->getList();
            $storeId = 0;

            // Setting same config data for all stores.
            foreach ($stores as $store) {
                $storeId = $store->getStoreId();
                $model = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Seller'
                )->getCollection()
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('store_id', $storeId);
                // If seller data doesn't exist for current store
                if (!count($model)) {
                    $model = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Seller'
                    )->getCollection()
                    ->addFieldToFilter('seller_id', $sellerId)
                    ->addFieldToFilter('store_id', 0);
                }
                $sellerData = $model->getFirstItem();
                $preparedData['store_id'] = $storeId; // Set store id in Config Data.
                foreach ($preparedData as $key => $value) {
                    $sellerData->setData($key, $value);
                }
                if($sellerData->save()){
                    $this->marketplaceHelper->getCustomer()->setTaxvat($preparedData['taxvat'])->save();
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/import',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        $message = __('Your profile data has been saved successfully.');
        $this->messageManager->addSuccess($message);
        return $this->resultRedirectFactory->create()->setPath('*/*/import');
    }
}
