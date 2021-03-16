<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Quote abstract Class
 * For getting cart data
 */

namespace Cnnb\Gtm\DataLayer\QuoteData;

use Magento\Quote\Model\Quote;

abstract class QuoteAbstract
{
    /**
     * @var QuoteProvider[]
     */
    protected $quoteProviders;

    /**
     * @var array
     */
    private $transactionData = [];

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @return array
     */
    abstract public function getData();

    /**
     * @return array
     */
    public function getTransactionData()
    {
        return (array) $this->transactionData;
    }

    /**
     * @param array $transactionData
     * @return QuoteAbstract
     */
    public function setTransactionData(array $transactionData)
    {
        $this->transactionData = $transactionData;
        return $this;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param Quote $quote
     * @return QuoteAbstract
     */
    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return QuoteProvider[]
     */
    public function getQuoteProviders()
    {
        return $this->quoteProviders;
    }
}
