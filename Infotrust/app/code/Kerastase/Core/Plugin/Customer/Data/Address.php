<?php


namespace Kerastase\Core\Plugin\Customer\Data;


use Magento\Framework\Encryption\EncryptorInterface;

class Address
{

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
        \Magento\Customer\Model\Address $address,
        \Kerastase\Core\Helper\Data $helper,
        \Kerastase\Core\Model\ResourceModel\Customer $customerResource

    ) {
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->address = $address;
        $this->helper = $helper;
        $this->customerResource = $customerResource;

    }

    /**
     * @param $addessId
     * @return bool
     */
    public function isAddressEncrypted($addessId)
    {

        $addressFactory = $this->address->load($addessId);
        if ($addressFactory->getData('is_encrypted') == 1  ) {
            return true;
        }
        return false;
    }

    /**
     * @param $result
     * @return bool
     */
    public function isDataEncrypted($result)
    {
        try{
            if(strpos($result, \Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START) !== false ) {
                return true;
            }

        } catch (\Exception $e) {
            $errorMessage = 'Exception::' . $e->getMessage();

        }

        return false;
    }

    /**
     * @param \Magento\Customer\Model\Data\Address $address
     * @param $result
     * @return string
     */

    public function afterGetCity(\Magento\Customer\Model\Data\Address $address , $result){

        if($result)  {
            if(!$this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('City Value not crypted but customer crypted',true);
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),0);
            }
            else if($this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('City Value  crypted and customer crypted',true);

                $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                return $this->encryptor->decrypt($result);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Customer\Model\Data\Address $address
     * @param $result
     * @return string
     */
    public function afterGetStreet(\Magento\Customer\Model\Data\Address $address , $result){

        if($result)  {
            if(!$this->isDataEncrypted($result[0]) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Street Value not crypted but address crypted',true);
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),0);
            }
            else if($this->isDataEncrypted($result[0]) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Street Value  crypted and Address crypted',true);

                $result[0] =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result[0]);
                return $this->encryptor->decrypt($result[0]);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Customer\Model\Data\Address $address
     * @param $result
     * @return string
     */
    public function afterGetCompany(\Magento\Customer\Model\Data\Address $address , $result){

        if($result)  {
            if(!$this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Campany Value not crypted but address crypted',true);
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),0);
            }
            else if($this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Campany Value  crypted and address crypted',true);

                $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                return $this->encryptor->decrypt($result);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Customer\Model\Data\Address $address
     * @param $result
     * @return string
     */
    public function afterGetTelephone(\Magento\Customer\Model\Data\Address $address , $result){

        if($result)  {
            if(!$this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Tel Value not crypted but address crypted',true);
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),0);
            }
            else if($this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Tel Value  crypted and address crypted',true);

                $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                return $this->encryptor->decrypt($result);
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Data\Address $address
     * @param $result
     * @return string
     */
    public function afterGetFax(\Magento\Customer\Model\Data\Address $address , $result){

        if($result)  {
            if(!$this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Fax Value not crypted but address crypted',true);
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),0);
            }
            else if($this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Fax Value  crypted and address crypted',true);

                $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                return $this->encryptor->decrypt($result);
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Data\Address $address
     * @param $result
     * @return string
     */
    public function afterGetPostcode(\Magento\Customer\Model\Data\Address $address , $result){

        if($result)  {
            if(!$this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('Post Code Value not crypted but address crypted',true);
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),0);
            }
            else if($this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('POst Code Value  crypted and address crypted',true);

                $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                return $this->encryptor->decrypt($result);
            }
        }
    }

    /**
     * @param \Magento\Customer\Model\Data\Address $address
     * @param $result
     * @return string
     */
    public function afterGetVatId(\Magento\Customer\Model\Data\Address $address , $result){

        if($result)  {
            if(!$this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('VAT Value not crypted but address crypted',true);
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),0);
            }
            else if($this->isDataEncrypted($result) && $this->isAddressEncrypted($address->getId())){
                $this->helper->log('VAT Value  crypted and address crypted',true);

                $result =  str_replace(\Kerastase\Core\Helper\Data::SALTY_ENCRYPTOR_START, '', $result);
                return $this->encryptor->decrypt($result);
            }
        }
    }
}