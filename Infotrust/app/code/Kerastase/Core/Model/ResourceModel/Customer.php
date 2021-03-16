<?php


namespace Kerastase\Core\Model\ResourceModel;


class Customer extends \Magento\Catalog\Model\ResourceModel\AbstractResource
{


    public function UpdateIsEncrypted($customer_id,$value){

        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $connection->update('customer_entity', ['is_encrypted' => $value], ['entity_id = ?' => $customer_id]);
            $connection->commit();
        } catch(\Exception $e) {
            $connection->rollBack();
        }



    }

    public function UpdateAddressIsEncrypted($address_id,$value){

        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $connection->update('customer_address_entity', ['is_encrypted' => $value], ['entity_id = ?' => $address_id]);
            $connection->commit();
        } catch(\Exception $e) {
            $connection->rollBack();
        }

    }


}
