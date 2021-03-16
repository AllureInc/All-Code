<?php
/**
 * Created by PhpStorm.
 * User: Dmytro Portenko
 * Date: 10/16/18
 * Time: 10:36 PM
 */
namespace Cor\Rest\Plugin\Model\Quote\Item;

class RepositoryPlugin
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function beforeSave(
        \Magento\Quote\Model\Quote\Item\Repository $subject,
        \Magento\Quote\Api\Data\CartItemInterface $cartItem
    )
    {
        $cartId = $cartItem->getQuoteId();
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->getShippingAddress();
    }
}