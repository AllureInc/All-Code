<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.2.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Model;

use Magento\Framework\DataObject;
use Mirasvit\Dashboard\Api\Data\BlockInterface;

class Block extends DataObject implements BlockInterface
{
    /**
     * {@inheritdoc}
     */
    public function setId($value)
    {
        return $this->setData(BlockInterface::ID, (int)$value);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(BlockInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getPos()
    {
        return $this->getData(BlockInterface::POS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPos($data)
    {
        return $this->setData(BlockInterface::POS, [(int)$data[0], (int)$data[1]]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->getData(BlockInterface::SIZE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($data)
    {
        return $this->setData(BlockInterface::SIZE, [(int)$data[0], (int)$data[1]]);
    }

    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function getIdentifier()
    //    {
    //        return $this->getData(WidgetInterface::IDENTIFIER);
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function setIdentifier($value)
    //    {
    //        return $this->setData(WidgetInterface::IDENTIFIER, $value);
    //    }
    //
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(BlockInterface::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($value)
    {
        return $this->setData(BlockInterface::TITLE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(BlockInterface::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($value)
    {
        return $this->setData(BlockInterface::DESCRIPTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function asArray()
    {
        $data = $this->getData();
        if (isset($data['report']['data']) && !is_array($data['report']['data'])) {
            $data['report']['data'] = [$data['report']['data']];
        }

        return $data;
    }
}