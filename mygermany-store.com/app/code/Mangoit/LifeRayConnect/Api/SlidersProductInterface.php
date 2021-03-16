<?php
namespace Mangoit\LifeRayConnect\Api;
 
interface SlidersProductInterface
{
    /**
     * It will return product collection based on type
     *
     * @api
     * @param string $type Type of product slider
     * @return string collection of products
     */
    public function slidersproduct($type);
}