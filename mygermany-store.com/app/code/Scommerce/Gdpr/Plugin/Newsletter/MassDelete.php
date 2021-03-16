<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\Gdpr\Plugin\Newsletter;

use Magento\Newsletter\Model\SubscriberFactory;
use Scommerce\Gdpr\Model\Service\ConsentService;

/**
 * Class MassDelete
 * @package Scommerce\Gdpr\Plugin\Newsletter
 */
class MassDelete
{
    /**
     * @var ConsentService
     */
    private $consentService;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * MassDelete constructor.
     * @param ConsentService $consentService
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        ConsentService $consentService,
        SubscriberFactory $subscriberFactory
    ) {
        $this->consentService = $consentService;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @param \Magento\Newsletter\Controller\Adminhtml\Subscriber\MassDelete $subject
     */
    public function beforeExecute(\Magento\Newsletter\Controller\Adminhtml\Subscriber\MassDelete $subject)
    {
        $subscribersIds = $subject->getRequest()->getParam('subscriber');
        if (is_array($subscribersIds)) {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->subscriberFactory->create()->load(
                        $subscriberId
                    );
                    $this->consentService->deleteConsentWithNewsletter($subscriber->getEmail());
                }
            } catch (\Exception $e) { }
        }
    }
}