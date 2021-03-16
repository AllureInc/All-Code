<?php

/**
 * Package 2018 Mangoit_Marketplace
 */

namespace Mangoit\Marketplace\Model\Attribute\Source;

class Deliverydaysto extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
   /**
    * Retrieve options array.
    *
    * @return array
    */
   public function toOptionArray()
   {
       $result = [];

       foreach (self::getOptionArray() as $index => $value) {
           $result[] = ['value' => $index, 'label' => $value, 'labeltitlee' => '__'.$value];
       }

       return $result;
   }

   /**
    * Retrieve option array
    *
    * @return string[]
    */
   public static function getOptionArray()
   {
        $optionsArray = [];
        for ($i=2; $i < 31 ; $i++) { 
            $optionsArray[$i] = __($i);
        }
        return $optionsArray;
   }

   /**
    * Retrieve option array with empty value
    *
    * @return string[]
    */
   public function getAllOptions()
   {
       $result = [];

       foreach (self::getOptionArray() as $index => $value) {
           $result[] = ['value' => $index, 'label' => $value];
       }

       return $result;
   }
}