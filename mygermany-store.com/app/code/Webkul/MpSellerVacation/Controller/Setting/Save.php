<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Controller\Setting;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Webkul\MpSellerVacation\Model\Vacation;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param Context          $context
     * @param PageFactory      $_resultPageFactory
     * @param Session          $customerSession    [customer session]
     * @param Vacation         $vacation           [seller vacation model]
     * @param FormKeyValidator $formKeyValidator   [form key validator]
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        DateTime $date,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {

        $this->timezone = $timezoneInterface;
        $this->_date = $date;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $_resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
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
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * save vacation setting form submit action handler.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /**
 * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
*/
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory
                        ->create()
                        ->setPath('*/*/index', ['_secure' => $this->getRequest()->isSecure()]);
                }
                $requestData = $this->getRequest()->getParams();
                if (!isset($requestData['seller_id'])) {
                    $requestData['seller_id'] = $this->marketplaceHelper->getCustomerId();
                }

                $result = $this->_saveSetting($requestData);

                if (isset($result['error'])) {
                    $this->messageManager->addError(__($result['error']));
                }
                if (isset($result['updated'])) {
                    $this->_updateSellerProducts($result['id']);
                    $this->messageManager->addSuccess(__($result['updated']));
                }
                if (isset($result['success'])) {
                    $this->_updateSellerProducts($result['id']);
                    $this->messageManager->addSuccess(__($result['success']));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }

        return $this->resultRedirectFactory
            ->create()
            ->setPath('*/*/index', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Save seller vacation setting.
     *
     * @param array $data Vacation Setting data
     */
    protected function _saveSetting($data)
    {

        $data['product_disable_type'] = $this->_getVacationHelper()->getVacationMode();

        if ($data['id']) {
            try {
                $data['date_from']=$this->converToTz(
                    $data['date_from'],
                    $data['time_zone'],
                    $this->timezone->getConfigTimezone()
                );
                $data['date_to']=$this->converToTz(
                    $data['date_to'],
                    $data['time_zone'],
                    $this->timezone->getConfigTimezone()
                );
                $vacation = $this->_objectManager
                    ->create('Webkul\MpSellerVacation\Model\Vacation')->load($data['id']);
                if ($vacation->getId()) {
                    $data['updated_at'] = $this->timezone->date()->format('Y-m-d h:i:s');
                    $data['updated_at'] = $this->converToTz(
                        $data['updated_at'],
                        $data['time_zone'],
                        $this->timezone->getConfigTimezone()
                    );
                    $vacation->setData($data)->save();
                    return [
                                'updated' => 'Vacation setting successfully updated',
                                'id' => $vacation->getId(),
                            ];
                } else {
                    return [
                                'error' => 'Vacation setting is not saved yet',
                            ];
                }
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }
        } else {
            unset($data['id']);
            try {
                $data['created_at'] = $this->timezone->date()->format('Y-m-d h:i:s');
                $data['created_at']=$this->converToTz(
                    $data['created_at'],
                    $data['time_zone'],
                    $this->timezone->getConfigTimezone()
                );
                $data['date_from']=$this->converToTz(
                    $data['date_from'],
                    $data['time_zone'],
                    $this->timezone->getConfigTimezone()
                );
                $data['date_to']=$this->converToTz(
                    $data['date_to'],
                    $data['time_zone'],
                    $this->timezone->getConfigTimezone()
                );
                $vacation = $this->_objectManager
                    ->create('Webkul\MpSellerVacation\Model\Vacation')
                    ->setData($data)->save();

                return [
                            'success' => 'Vacation setting successfully saved',
                            'id' => $vacation->getId(),
                        ];
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }
        }
    }

    /**
     * function to disable all the seller products if he selected disable product.
     *
     * @param int $vacationId
     */
    protected function _updateSellerProducts($vacationId)
    {
        $vacation = $this->_objectManager
            ->get('Webkul\MpSellerVacation\Model\Vacation')
            ->load($vacationId);
        /*
         * detarmine if seller is on vacation or not
         *
         * @var boolean
         */
        $isTimeToDisable = $this->_getVacationHelper()->checkDisableTime($vacation);

        $sellerId = $vacation->getSellerId();
        if ($isTimeToDisable) {
            if ($this->_getVacationHelper()->getVacationMode($vacation) == 'product_disable') {
                $this->_getVacationHelper()->disableSellerProducts($sellerId);
            } else {
                $this->_getVacationHelper()->enableSellerProducts($sellerId);
            }
        } else {
            $this->_getVacationHelper()->enableSellerProducts($sellerId);
        }
    }

    /**
     * get MpSellerVacation Helper.
     *
     * @return Webkul\MpSellerVacation\Helper\Data
     */
    protected function _getVacationHelper()
    {
        return $this->_objectManager->create('Webkul\MpSellerVacation\Helper\Data');
    }
     /**
      * this is used to convert into ConfigTimezone form DefaultTimezone.
      *
      * @param string dateTime - time tobe converted.
      *
      * @param string timeZone inwhich you want to convert.
      *
      * @param string timeZone fromwhich.
      *
      * @return string datetime according to 2'nd Param.
      */
    protected function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        // timezone by php friendly values
        date_default_timezone_set($toTz);
        $date = new \DateTime($dateTime);
        $la_time = new \DateTimeZone($fromTz);
        $date->setTimezone($la_time);
        $dateTime = $date->format('Y-m-d H:i:s');
        return $dateTime;
    }
}
