<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.20
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Reports\Cron;

use Mirasvit\Report\Model\ResourceModel\Email\CollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Report\Api\Service\EmailServiceInterface;

class Email
{
    const JOB_CODE = 'reports_email';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Schedule
     */
    protected $schedule;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var EmailServiceInterface
     */
    protected $emailService;

    public function __construct(
        CollectionFactory $collectionFactory,
        Schedule $schedule,
        TimezoneInterface $timezone,
        EmailServiceInterface $emailService
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->schedule = $schedule;
        $this->timezone = $timezone;
        $this->emailService = $emailService;
    }

    /**
     * @param bool $verbose
     *
     * @return void
     */
    public function execute($verbose = true)
    {
        /** @var \Mirasvit\Report\Model\Email $email */
        foreach ($this->collectionFactory->create() as $email) {
            $email = $email->load($email->getId());

            if (!$email->getIsActive()
                // do not send the same email twice. It's possible when Magento cron does not run every minute.
                || date('Y-m-d H:i', strtotime($email->getLastSentAt())) === date('Y-m-d H:i', time())
            ) {
                continue;
            }

            $this->schedule
                ->setCronExpr($email->getSchedule())
                ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $this->timezone->date()->getTimestamp()));

            if ($this->schedule->trySchedule()) {
                if ($verbose) {
                    echo 'Send email to: '.$email->getRecipient().PHP_EOL;
                }

                $this->emailService->send($email);

                // update last_sent_at field
                $email->setLastSentAt(date('Y-m-d H:i:s', time()));
                $email->save();
            } else {
                if ($verbose) {
                    echo 'Skip send email to: '.$email->getRecipient().PHP_EOL;
                }
            }
        }
    }
}