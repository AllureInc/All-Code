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
namespace Webkul\MpSellerVacation\Model;

use Magento\Framework\Logger\Monolog as Logger;

class Cron
{
    /**
     * for logging.
     *
     * @var Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Webkul\MpSellerVacation\Helper\Data
     */
    protected $_vacationHelper;

    /**
     * @param Logger                                    $logger
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        Logger $logger,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\MpSellerVacation\Helper\Data $vacationHelper
    ) {

        $this->_logger = $logger;
        $this->_objectManager = $objectManager;
        $this->_vacationHelper = $vacationHelper;
    }
    /**
     * cron job to check if vacation is over resume the product status.
     */
    public function vacationUpdate()
    {
        $collection = $this->_objectManager->create('\Webkul\MpSellerVacation\Model\ResourceModel\Vacation\Collection');
        if ($collection->getSize() > 0) {
            $debugMessage = [];
            $vacationConfig = $this->_vacationHelper->getVacationMode();
            foreach ($collection as $vacation) {
                $vacationConfig = $this->_vacationHelper->getVacationMode($vacation);
                $customer = $this->_objectManager
                    ->create('Magento\Customer\Model\Customer')
                    ->load($vacation->getSellerId());
                if ($customer) {
                    $status = $this->_vacationHelper->checkDisableTime($vacation);

                    if ($status && $vacationConfig == 'product_disable') {
                        $this->_vacationHelper->disableSellerProducts($vacation->getSellerId());
                        $debugMessage[$customer->getId()] = 'Seller :'.$customer->getName().' All Products Disabled';
                    } else {
                        $this->_vacationHelper->enableSellerProducts($vacation->getSellerId());
                        $debugMessage[$customer->getId()] = 'Seller :'.$customer->getName().' All Products Enabled';
                    }
                }
            }
            $this->_logger->debug(json_encode($debugMessage));
        } else {
            $this->_logger->debug('No Vacation Settings Found');
        }
    }
}
