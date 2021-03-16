<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Api\Data;

/**
 * Interface LogSearchResultsInterface
 * @package Kerastase\EnhancedSMTP\Api\Data
 */
interface ActivitySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get admin activity list.
     * @api
     * @return \Kerastase\AdminActivity\Model\Activity[]
     */
    public function getItems();

    /**
     * Set admin activity list.
     * @api
     * @param \Kerastase\AdminActivity\Model\Activity[] $items
     * @return $this
     */
    public function setItems(array $items);
}
