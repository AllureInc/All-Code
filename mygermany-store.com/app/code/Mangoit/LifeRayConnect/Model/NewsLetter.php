<?php

namespace Mangoit\LifeRayConnect\Model;
use Mangoit\LifeRayConnect\Api\NewsLetterInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
* Magento\Newsletter\Model\Subscriber
* const STATUS_SUBSCRIBED = 1;
    const STATUS_NOT_ACTIVE = 2;
    const STATUS_UNSUBSCRIBED = 3;
    const STATUS_UNCONFIRMED = 4;
* 
* throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested product doesn\'t exist'));
*/
class NewsLetter implements NewsLetterInterface 
{
	protected $_subscriberFactory;
	protected $_customer;

	public function __construct(
		\Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
		 \Magento\Customer\Model\Customer $customer
		 )
	{
		$this->_subscriberFactory = $subscriberFactory;
		$this->_customer = $customer;
	}

	public function issubscribeuser($email, $issubscribed)
	{
		$this->validation($email, $issubscribed);
		$subscribeObj = $this->_subscriberFactory->create();
		$customer = $this->_customer->loadByEmail($email);
		 if ($customer->getId()) {
            if ($issubscribed) {
				$subscribeObj->subscribe($email);
			} else {
				$subscribeObj->loadByEmail($email)->unsubscribe();
			}
        } else {
            throw new InputException(__('No customer found!'));
        }
		
		return ['status'=> '200', 'message' => 'You have successfully updated newsletter'];
		// if ($modelLoad->getData()) {
		// 	if ($issubscribed == 3) {
		// 		$modelLoad->setSubscriberStatus(3);
		// 		$modelLoad->save();
		// 		return ['status'=> '200', 'message' => 'User has been subscribed for newsletter.'];
		// 	} else if ($issubscribed == 1) {
		// 		$modelLoad->setSubscriberStatus(1);
		// 		$modelLoad->save();
		// 		return ['status'=> '200', 'message' => 'User has been subscribed for newsletter.'];
		// 	} else if ($issubscribed == 2) {
		// 		$modelLoad->setSubscriberStatus(2);
		// 		$modelLoad->save();
		// 		return ['status'=> '200', 'message' => 'User status is set "Not Active" for newsletter.'];
		// 	} else if ($issubscribed == 4) {
		// 		$modelLoad->setSubscriberStatus(4);
		// 		$modelLoad->save();
		// 		return ['status'=> '200', 'message' => 'User unconfirmed for newsletter.'];
		// 	} else {
		// 		throw new \Magento\Framework\Exception\NoSuchEntityException(__(
		// 			'Wrong status for newsletter'
		// 			));
		// 	}
			
			
		// } else {
		// 	throw new NoSuchEntityException(__('user not found with this id: '.$id));
		// }
	}

	public function validation($email, $status)
    {
        $statuses = [0, 1];
        $textOnly = '/^[a-zA-Z ]+$/';
        $emailOnly = '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/';
        if (strlen(trim($email)) > 0) {
            if (!preg_match($emailOnly, trim($email))) {
                throw new \Magento\Framework\Webapi\Exception(__('Email id is not valid.'));
            }           
        } else {
            throw new \Magento\Framework\Webapi\Exception(__('Email id is not valid'));
        }
        if (!in_array($status, $statuses)) {
            throw new InputException(__('Wrong input!'));
        }
    }
}