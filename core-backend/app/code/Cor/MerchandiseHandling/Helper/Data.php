<?php
/**
 * Module: Cor_MerchandiseHandling
 * Helper File
 * Helper methods for generating reports.
 */
namespace Cor\MerchandiseHandling\Helper;
/**
 * Main Class
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory 
     */
    protected $_orderCollectionFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * Class Constructor
     */
    function __construct( 
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderCollectionFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_logger = $logger;
        $this->_objectManager = $objectmanager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Method for get logger
     * @return logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Method for retrive all data for event pdf report.
     * @return Array eventPdfDetails []
     */
    public function getEventPdfDetails($event_id, $artist_id = 0){
        $eventPdfDetails = array();

        if ($event_id) {
            $artist = array();
            if ($artist_id) {
                $artist = $this->getArtistDetails($artist_id);
                $artist = $artist->getData();
                $this->_logger->info('Generating settlement report for '.$artist['artist_name']);
                if (isset($artist['wnine_received'])) {
                    $artist['wnine_received'] = ($artist['wnine_received'] == 1) ? 'Yes' : 'No' ;
                }
            }
            $event = $this->getEventDetails($event_id);
            $event = $event->getData();
            $artistCategories = $this->getArtistCategories();

            $event_start_date = date_create($event['event_start_date']);
            $date = date_format($event_start_date, "j");
            $month = date_format($event_start_date, "M");

            $event['event_date'] = $date;
            $event['event_month'] = $month;

            if ($event['tax_values'] != '') {
                $tax_values = json_decode($event['tax_values'] , true);
                $event['tax_values'] = array();
                foreach ($tax_values as $category_id => $tax_percent) {
                    if (isset($artistCategories[$category_id])) {
                        $category_name = $artistCategories[$category_id];
                        $event['tax_values'][$category_id] = $tax_percent;
                    }
                }
            }

            $eventArtists = $this->getEventArtistDetails($event_id, $artist_id);

            $eventArtistCut = array();
            if (count($eventArtists) > 0) {
                foreach ($eventArtists as $eventArtist) {
                    $artistCutsData = json_decode($eventArtist['artist_cut'] , true);
                    $artist_cuts = array();
                    foreach ($artistCutsData as $category_id => $cut_percent) {
                        if (isset($artistCategories[$category_id])) {
                            $category_name = $artistCategories[$category_id];
                            $artist_cuts[$category_id]['label'] = $category_name;
                            $artist_cuts[$category_id]['percent'] = $cut_percent;
                            $artist_cuts[$category_id]['amount'] = 0;
                            $artist_cuts[$category_id]['artist_cut'] = 0;
                            $artist_cuts[$category_id]['venue_cut'] = 0;
                        }
                    }
                    $eventArtistCut[$eventArtist['artist_id']] = $artist_cuts;
                    if ($artist_id && !empty($artist)) {
                        $artist['cut_detail'] = $artist_cuts;
                    }
                }
            }

            $storeValue = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

            $apparel_category_id = $storeValue->getValue('artist_category/category_id/apparel_tax');
            $other_category_id = $storeValue->getValue('artist_category/category_id/other_tax');
            $music_category_id = $storeValue->getValue('artist_category/category_id/music_tax');

            $apparel_tax_percent = 0;
            $other_tax_percent = 0;
            $music_tax_percent = 0;

            if (isset($event['tax_values'][$apparel_category_id]) && !empty($event['tax_values'][$apparel_category_id])) {
                $apparel_tax_percent = $event['tax_values'][$apparel_category_id];
            } else if (isset($event['tax_values'][$other_category_id]) && !empty($event['tax_values'][$other_category_id])) {
                $other_tax_percent = $event['tax_values'][$other_category_id];
            } else if (isset($event['tax_values'][$music_category_id]) && !empty($event['tax_values'][$music_category_id])) {
                $music_tax_percent = $event['tax_values'][$music_category_id];
            }

            $orderItemDetails = $this->getOrderItemDetails($event_id, $artist_id);

            $totalPaymentsByBraintree = 0;
            $totalPaymentsByCash = 0;
            $totalShippingAmount = 0;

            $apparel_gross_sale = 0;
            $other_gross_sale = 0;
            $music_gross_sale = 0;

            foreach ($orderItemDetails as $orderData) {
                extract($orderData);

                $model = $this->_objectManager->create('Magento\Sales\Model\Order');
                $order = $model->load($order_id, 'entity_id');
                $payment = $order->getPayment();

                if ($payment->getMethod() == 'braintree') {
                    $totalPaymentsByBraintree += $order->getGrandTotal();
                } else if($payment->getMethod() == 'cashondelivery'){
                    $totalPaymentsByCash += $order->getGrandTotal();
                }

                $totalShippingAmount += $order->getBaseShippingAmount();
                $eventPdfLogger = [];
                $orderItems = $order->getAllVisibleItems();
                foreach ($orderItems as $orderItem) {
                    $eventPdfLogger['product name'] = $orderItem->getName();
                    $artist_id = $orderItem->getCorArtistId();
                    $artist_category_id = $orderItem->getCorArtistCategoryId();
                    if (isset($eventArtistCut[$artist_id]) && isset($eventArtistCut[$artist_id][$artist_category_id])) {
                        $amount = $orderItem->getRowTotal();
                        $eventPdfLogger['product amount'] = $orderItem->getRowTotal();
                        if ($apparel_category_id == $artist_category_id) {
                            $apparel_gross_sale += $amount;
                            $cut_percent = $eventArtistCut[$artist_id][$artist_category_id]['percent'];
                            $eventArtistCut[$artist_id][$artist_category_id]['amount'] = $apparel_gross_sale;
                            $eventArtistCut[$artist_id][$artist_category_id]['artist_cut'] = $this->getArtistCutCalculated($apparel_gross_sale, $cut_percent);
                            $eventArtistCut[$artist_id][$artist_category_id]['venue_cut'] = $this->getVenueCutCalculated($apparel_gross_sale, $cut_percent);
                        } elseif ($other_category_id == $artist_category_id) {
                            $other_gross_sale += $amount;
                            $cut_percent = $eventArtistCut[$artist_id][$artist_category_id]['percent'];
                            $eventArtistCut[$artist_id][$artist_category_id]['amount'] = $other_gross_sale;
                            $eventArtistCut[$artist_id][$artist_category_id]['artist_cut'] = $this->getArtistCutCalculated($other_gross_sale, $cut_percent);
                            $eventArtistCut[$artist_id][$artist_category_id]['venue_cut'] = $this->getVenueCutCalculated($other_gross_sale, $cut_percent);
                        } elseif ($music_category_id == $artist_category_id) {
                            $music_gross_sale += $amount;
                            $cut_percent = $eventArtistCut[$artist_id][$artist_category_id]['percent'];
                            $eventArtistCut[$artist_id][$artist_category_id]['amount'] = $music_gross_sale;
                            $eventArtistCut[$artist_id][$artist_category_id]['artist_cut'] = $this->getArtistCutCalculated($music_gross_sale, $cut_percent);
                            $eventArtistCut[$artist_id][$artist_category_id]['venue_cut'] = $this->getVenueCutCalculated($music_gross_sale, $cut_percent);
                        }
                    }
                }
            }

            $apparel_tax_amount = $this->getTaxCalculated($apparel_gross_sale, $apparel_tax_percent);

            $apparel['gross_sale'] = $apparel_gross_sale;
            $apparel['tax_amount'] = $apparel_tax_amount;

            $other_tax_amount = $this->getTaxCalculated($other_gross_sale, $other_tax_percent);

            $other['gross_sale'] = $other_gross_sale;
            $other['tax_amount'] = $other_tax_amount;

            $music_tax_amount = $this->getTaxCalculated($music_gross_sale, $music_tax_percent);
            $music_total_gross_adjusted = $music_gross_sale - $music_tax_amount;

            $music['gross_sale'] = $music_gross_sale;
            $music['tax_amount'] = $music_tax_amount;
            $music['total_gross_adjusted'] = $music_total_gross_adjusted;

            $aot_gross_sale = $apparel_gross_sale + $other_gross_sale;
            $aot_fee_amount = $this->getFeeCalculated($aot_gross_sale);
            $aot_tax_amount = $apparel_tax_amount + $other_tax_amount;
            $aot_gross_adjusted = $aot_gross_sale - $aot_fee_amount - $aot_tax_amount;

            $apparel_other['total_gross_sale'] = $aot_gross_sale;
            $apparel_other['total_fee_amount'] = $aot_fee_amount;
            $apparel_other['total_tax_amount'] = $aot_tax_amount;
            $apparel_other['total_gross_adjusted'] = $aot_gross_adjusted;

            $artist_apparel_cut = 0;
            $artist_other_cut = 0;
            $artist_music_cut = 0;

            $venue_apparel_cut = 0;
            $venue_other_cut = 0;
            $venue_music_cut = 0;

            foreach ($eventArtistCut as $artistId => $artistCategoryData) {
                foreach ($artistCategoryData as $categoryId => $artistCutData) {
                    if ($apparel_category_id == $categoryId) {
                        $artist_apparel_cut += $artistCutData['artist_cut'] ;
                        $venue_apparel_cut += $artistCutData['venue_cut'] ;
                    } else if ($other_category_id == $categoryId) {
                        $artist_other_cut += $artistCutData['artist_cut'] ;
                        $venue_other_cut += $artistCutData['venue_cut'] ;
                    } else if ($music_category_id == $categoryId) {
                        $artist_music_cut += $artistCutData['artist_cut'] ;
                        $venue_music_cut += $artistCutData['venue_cut'] ;
                    }
                }
            }

            $braintreeFeeAmount = $this->getFeeCalculated($totalPaymentsByBraintree);
            $grossTotal = $totalPaymentsByBraintree + $totalPaymentsByCash;

            $artist_merch_cut = $artist_apparel_cut + $artist_other_cut;
            $artist_total_due = $artist_merch_cut + $artist_music_cut;

            $artist_settlement['merch_cut'] = $artist_merch_cut;
            $artist_settlement['music_cut'] = $artist_music_cut;
            $artist_settlement['total_due'] = $artist_total_due;

            $venue_merch_cut = $venue_apparel_cut + $venue_other_cut;
            $venue_total_due = $venue_merch_cut + $venue_music_cut + $music_tax_amount + $braintreeFeeAmount + $totalShippingAmount;

            $venue_settlement['merch_cut'] = $venue_merch_cut;
            $venue_settlement['music_cut'] = $venue_music_cut;
            $venue_settlement['total_shipping_amount'] = $totalShippingAmount;
            $venue_settlement['total_due'] = $venue_total_due;

            $eventPdfDetails['event'] = $event;
            $eventPdfDetails['artist'] = $artist;
            $eventPdfDetails['artists_cuts'] = $eventArtistCut;
            $eventPdfDetails['total_payment_braintree'] = $totalPaymentsByBraintree;
            $eventPdfDetails['total_payment_cash'] = $totalPaymentsByCash;
            $eventPdfDetails['fee_amount_braintree'] = $braintreeFeeAmount;
            $eventPdfDetails['gross_total'] = $grossTotal;
            $eventPdfDetails['apparel'] = $apparel;
            $eventPdfDetails['other'] = $other;
            $eventPdfDetails['music'] = $music;
            $eventPdfDetails['apparel_other'] = $apparel_other;
            $eventPdfDetails['artist_settlement'] = $artist_settlement;
            $eventPdfDetails['venue_settlement'] = $venue_settlement;
        }

        return $eventPdfDetails;
    }

    /**
     * Method for calculate tax
     * @return int|float tax
     */
    public function getTaxCalculated($amount, $percent){
        $tax = 0;
        if ($amount && $percent) {
            $tax = ($amount * $percent) / 100;
        }
        return $tax;
    }

    /**
     * Method for calculate cut
     * @return int|float cut
     */
    public function getArtistCutCalculated($amount, $percent){
        $cut = 0;
        if ($amount && $percent) {
            $cut = ($amount * $percent) / 100;
        }
        return $cut;
    }

    /**
     * Method for calculate cut
     * @return int|float cut
     */
    public function getVenueCutCalculated($amount, $percent){
        $cut = 0;
        $cut_percent = 100 - (float)$percent;
        if ($amount && $cut_percent) {
            $cut = ($amount * $cut_percent) / 100;
        }
        return $cut;
    }

    /**
     * Method for calculate fee
     * @return int|float fee
     */
    public function getFeeCalculated($amount){
        $fee = 0;
        $percent = 2.750;
        if ($amount) {
            $fee = ($amount * $percent) / 100;
        }
        return $fee;
    }

    /**
     * Method to retrive artist category.
     * @return string artistCategory
     */
    public function getArtistCategories()
    {
        $model = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory');
        $collection = $model->getCollection();
        $categoriesData = $collection->getData();
        
        $categories = array();
        foreach ($categoriesData as $category) {
            if ($category['status'] == 1) {
                $categories[$category['id']] = $category['category_name'];
            }
        }
        return $categories;
    }

    /**
     * Method to retrieve order item collection
     * @return array collection[]
     */
    public function getOrderItemDetails($event_id, $artist_id){
        $orderItemsCollection = $this->_orderCollectionFactory->create();
        $orderItemsCollection->AddAttributeToSelect('order_id');
        $orderItemsCollection->addFieldToFilter('cor_event_id', ['eq'=> $event_id]);
        if ($artist_id) {
            $orderItemsCollection->addFieldToFilter('cor_artist_id', ['eq'=> $artist_id]);
        }
        $orderItemsCollection->getSelect()->group('order_id');
        return $orderItemsCollection->getData();
    }

    /**
     * Method for retrive event artist cut % details.
     * @return array artist_cut
     */
    public function getEventArtistDetails($event_id, $artist_id)
    {
        $eventArtistModel = $this->_objectManager->create('Cor\Eventmanagement\Model\Eventartist');
        $collection = $eventArtistModel->getCollection();
        $collection->addFieldToFilter('event_id', ['eq'=> $event_id]);
        if ($artist_id) {
            $collection->addFieldToFilter('artist_id', ['eq'=> $artist_id]);
        }
        return $collection->getData();
    }

    /**
     * Method to format price amount
     * @return int|float price
     */
    public function formatPrice($amount){
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');
        return $priceHelper->currency($amount, true, false);
    }

    /**
     * Method for retrive artist details.
     * @return object artistModel
     */
    public function getArtistDetails($artist_id)
    {
        $artistModel = $this->_objectManager->create('Cor\Artist\Model\Artist');
        $artist = $artistModel->load($artist_id);
        return $artist;
    }

    /**
     * Method for update artist details.
     * @return boolean true|false
     */
    public function setArtistDetails($artist_id, $updateData)
    {
        $artistModel = $this->_objectManager->create('Cor\Artist\Model\Artist');
        $artist = $artistModel->load($artist_id);
        $artist->setData($updateData);
        $artist->save();
        return true;
    }

    /**
     * Method for retrive event details.
     * @return object eventmodel
     */
    public function getEventDetails($event_id)
    {
        $eventModel = $this->_objectManager->create('Cor\Eventmanagement\Model\Event');
        $event = $eventModel->load($event_id);
        return $event;
    }
}
