<?php

/**
 * Sparsh_SalesEmailAttachments
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SalesEmailAttachments\Model\Config\Backend;

/**
 * Class TacFile
 *
 * @category Sparsh
 * @package  Sparsh_SalesEmailAttachments
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class TacFile extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    public function _getAllowedExtensions()
    {
        return ['pdf', 'doc', 'docx', 'txt'];
    }
}
