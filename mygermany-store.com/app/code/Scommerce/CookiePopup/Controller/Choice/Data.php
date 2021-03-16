<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Choice;

/**
 * Class Save
 * @package Scommerce\CookiePopup\Controller\Choice
 */
class Data extends \Magento\Framework\App\Action\Action
{
    protected $preferenceBlock;

    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Scommerce\CookiePopup\Block\Preference $preferenceBlock,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->preferenceBlock = $preferenceBlock;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultData = $this->preferenceBlock->getPreparedChioices();
        return $this->resultJsonFactory->create()->setData(['choices' => $resultData['choices'], 'customerChoices' => $resultData['customerChoices']]);
    }
}
