<?php
/**
 * @category  Cnnb
 * @package   Kerastase_GiftRule
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the store codes
 */
namespace Kerastase\GiftRule\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * StoreCodeColumn | Admin Block Class
 */
class SourceCodeColumn extends Select
{
    /**
     * @var $_storeRepository
     */
    protected $_storeRepository;

    /**
     * @param Context $context
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     *
     */
    public function __construct(
        Context $context,
        \Magento\Inventory\Model\ResourceModel\Source\Collection $storeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeRepository = $storeRepository;
    }
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Provides source code Options
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $sourceListArr = $this->_storeRepository->load();
        $sourceCodeData[] = ['label'=> 'select','value'=> ''];
        foreach ($sourceListArr as $sourceItemName) {
           $sourceCodeName = $sourceItemName->getName();
           $sourceCode = $sourceItemName->getSourceCode();
           $sourceCodeData[] = [
            'label' => $sourceCodeName,
            'value' => $sourceCode
           ];
        }

        return $sourceCodeData;
    }
}
