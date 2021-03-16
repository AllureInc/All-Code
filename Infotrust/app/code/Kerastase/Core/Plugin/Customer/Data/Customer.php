<?php


namespace Kerastase\Core\Plugin\Customer\Data;


use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Setup\Exception;

class Customer
{



    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const MIDDLENAME ='middlename';
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;
    /**
     * @var \Kerastase\Core\Helper\Data
     */
    private $helper;
    /**
     * @var \Kerastase\Core\Model\ResourceModel\Customer
     */
    private $customerResource;

    public function __construct(
        EncryptorInterface $encryptor,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Customer $customer,
        \Kerastase\Core\Helper\Data $helper,
        \Kerastase\Core\Model\ResourceModel\Customer $customerResource

    ) {
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->customer = $customer;
        $this->helper = $helper;
        $this->customerResource = $customerResource;

    }

    public function isDataEncrypted($result)
    {
        try{
            if(strpos($result, \Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START) !== false ) {
                return true;
            }

        } catch (\Exception $e) {
            $errorMessage = 'Exception::' . $e->getMessage();
            $this->helper->log($errorMessage, true);
        }

        return false;
    }

    public function isCustomerEncrypted($customer)
    {
        if($customer->getId()){
            $customerFactory = $this->customer->load($customer->getId());

            if ($customerFactory->getData('is_encrypted') == 1 ) {
                return true;
            }
        }

        return false;
    }




    /**
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @param $result
     * @return string
     */
    public function afterGetFirstname (\Magento\Customer\Model\Data\Customer $customer, $result)
    {
          if($result)  {
              if(!$this->isDataEncrypted($result) && $this->isCustomerEncrypted($customer)){
                  $this->helper->log('Firstname Value not crypted but customer crypted',true);
                  $this->customerResource->UpdateIsEncrypted($customer->getId(),0);
              }
              else if($this->isDataEncrypted($result) && $this->isCustomerEncrypted($customer)){
                  $this->helper->log('Firstname Value  crypted and customer crypted',true);

                  $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                  return $this->encryptor->decrypt($result);
              }
          }

        return $result;

    }

    /**
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @param $result
     * @return string
     */
    public function afterGetLastname (\Magento\Customer\Model\Data\Customer $customer, $result)
    {
        if($result) {
            if (!$this->isDataEncrypted($result) && $this->isCustomerEncrypted($customer)) {
                $this->helper->log('Lastname Value not crypted but customer crypted', true);
                $this->customerResource->UpdateIsEncrypted($customer->getId(), 0);


            } else if ($this->isDataEncrypted($result) && $this->isCustomerEncrypted($customer)) {
                $result = str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                return $this->encryptor->decrypt($result);
            }
        }
        return $result;
    }


    public function afterGetMiddlename(\Magento\Customer\Model\Data\Customer $customer, $result){

       if($result){
           if(!$this->isDataEncrypted($result) && $this->isCustomerEncrypted($customer)){
               $this->helper->log('Middlename Value not crypted but customer crypted',true);
               $this->customerResource->UpdateIsEncrypted($customer->getId(),0);
           }
           else if($this->isDataEncrypted($result) && $this->isCustomerEncrypted($customer)){
               $this->helper->log('Middlename Value crypted and customer crypted',true);
               $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
               return $this->encryptor->decrypt($result);
           }
       }

        return $result;
    }



}