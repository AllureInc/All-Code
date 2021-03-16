<?php

namespace Mangoit\Ticket\Model;

use Potato\Zendesk\Api\TicketManagementInterface;
use Potato\Zendesk\Model\Authorization;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Zendesk\API\Exceptions\ApiResponseException;
use Potato\Zendesk\Api\Data\TicketInterface;
use Potato\Zendesk\Api\Data\TicketInterfaceFactory;
use Potato\Zendesk\Api\Data\MessageInterface;
use Potato\Zendesk\Api\Data\MessageInterfaceFactory;
use Potato\Zendesk\Api\Data\AttachmentInterface;
use Potato\Zendesk\Api\Data\AttachmentInterfaceFactory;
use Potato\Zendesk\Api\Data\UserInterface;
use Potato\Zendesk\Api\Data\UserInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\Store;
use Magento\Sales\Api\OrderRepositoryInterface;
use Zendesk\API\HttpClient as ZendeskAPI;
use Zendesk\API\Exceptions\MissingParametersException;
use Zendesk\API\Exceptions\CustomException;
use Psr\Log\LoggerInterface;
/**
 * Class Customer
 */
class Ticket extends \Potato\Zendesk\Model\Management\Ticket
{

    /**
     * @param array $ticketData
     * @param int|Store|null $store
     * @param array $attachments
     * @return null|\stdClass
     * @throws \Exception
     */
    public function updateTicket($ticketData, $store, $attachments = [])
    {
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            throw new \Exception(__('Authorization to Zendesk failed'));
        }
        $attachmentList = $this->prepareAttachments($client, $attachments);
        if (null === $customer = $this->getCustomer()) {
            throw new \Exception(__('Customer not found'));
        }
        $params = ['query' => $customer->getEmail()];
        $user = $this->searchUserByParams($params, $client);

        $params = [
            'status' => 'open',
            'comment'  => [
                'html_body' => $ticketData['comment'],
                'author_id' => $user->getId()
            ]

        ];
        if (!empty($attachmentList)) {
            $params['comment']['uploads'] = $attachmentList;
        }
        return $client->tickets()->update($ticketData['ticket_id'], $params);
    }

    /**
     * @param array $ticketData
     * @param int|Store|null $store
     * @param array $attachments
     * @return null|\stdClass
     * @throws \Exception
     */
    public function createTicket($ticketData, $store, $attachments = [])
    {
        $vendorMail = $this->getVendorMailId();
        $client = $this->authorization->connectToZendesk($store);
        if (null === $client) {
            throw new \Exception(__('Authorization to Zendesk failed'));
        }
        $attachmentList = $this->prepareAttachments($client, $attachments);
        if (null !== $customer = $this->getCustomer()) {
            $email = $customer->getEmail();
            $name = $customer->getFirstname() . ' ' . $customer->getLastname();
        } elseif (array_key_exists('order_id', $ticketData)) {
            $order = $this->orderRepository->get($ticketData['order_id']);
            $email = $order->getCustomerEmail();
            $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        } elseif (array_key_exists('id', $ticketData)) {
            $customer = $this->customerRepository->getById($ticketData['id']);
            $email = $customer->getEmail();
            $name = $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            throw new \Exception(__('Customer not found'));
        }
        $params = [
            'priority' => 'high',
            'comment'  => [
                'html_body' => $ticketData['comment']
            ],
            'subject'  => $ticketData['subject'],
            'requester' => [
                'name' => $name,
                'email' => $email,
            ],
            'collaborators' => $vendorMail
        ];
        if (!empty($attachmentList)) {
            $params['comment']['uploads'] = $attachmentList;
        }
        return $client->tickets()->create($params);
    }

    public function prepareAttachments($client, $attachments = [])
    {
        $attachmentList = [];
        if (empty($attachments) || !array_key_exists('error', $attachments)) {
            return $attachmentList;
        }
        foreach ($attachments["error"] as $key => $error) {
            if ($error !== UPLOAD_ERR_OK) {
                continue;
            }
            $uploadedFile = $client->attachments()->upload([
                'file' => $attachments['tmp_name'][$key],
                'type' => $attachments['type'][$key],
                'name' => $attachments['name'][$key]
            ]);
            $attachmentList[] = $uploadedFile->upload->token;

        }
        return $attachmentList;
    }

    public function searchUserByParams($params, $client)
    {
        $search = $client->users()->search($params);
        foreach ($search->users as $user) {
            $userData = [
                UserInterface::ID => $user->id,
                UserInterface::NAME => $user->name,
                UserInterface::ROLE => $user->role,
            ];
            if (null !== $user->photo) {
                $userData[UserInterface::PHOTO] = $user->photo->content_url;
            }
            return $this->userFactory->create(['data' => $userData]);
        }
        return $this->userFactory->create();
    }

    public function getVendorMailId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');
        $session->start();
        return $session->getVendorEmail();
    }
}
