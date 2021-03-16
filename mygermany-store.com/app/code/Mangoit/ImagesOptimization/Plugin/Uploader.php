<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\ImagesOptimization\Plugin;

// use Magento\Framework\Filesystem\DriverInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory as OptimizerChain;

class Uploader
{
    public function afterSave(\Magento\Framework\File\Uploader $subject, $result)
    {
        if(class_exists('Spatie\ImageOptimizer\OptimizerChainFactory') && isset($result['path']) && isset($result['file'])) {
            $optimizerChain = OptimizerChain::create();
            $optimizerChain->optimize($result['path'].'/'.$result['file']);
        }
        return $result;
    }
}