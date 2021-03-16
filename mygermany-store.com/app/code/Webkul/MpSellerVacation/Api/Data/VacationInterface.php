<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Api\Data;

interface VacationInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
         const ENTITY_ID = 'id';
         /**#@-*/

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpSellerVacation\Api\Data\VacationInterface
     */
    public function setId($id);
}
