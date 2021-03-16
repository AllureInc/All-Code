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



namespace Mirasvit\Dashboard\Ui\Board\Form;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var int
     */
    private $boardId;

    /**
     * @var BoardRepositoryInterface
     */
    private $boardRepository;

    /**
     * @var UrlInterface
     */
    private $urlManager;

    public function __construct(
        BoardRepositoryInterface $boardRepository,
        UrlInterface $urlManager,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->boardRepository = $boardRepository;
        $this->urlManager = $urlManager;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->boardId = $filter->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];

        $board = $this->boardRepository->get($this->boardId);

        if ($board) {
            $data[$this->boardId] = [
                BoardInterface::ID                => $board->getId(),
                BoardInterface::TITLE             => $board->getTitle(),
                BoardInterface::TYPE              => $board->getType(),
                BoardInterface::IS_DEFAULT        => $board->isDefault(),
                BoardInterface::IS_MOBILE_ENABLED => $board->isMobileEnable(),
                BoardInterface::MOBILE_TOKEN      => $board->getMobileToken(),
                'mobileUrl'                       => $this->urlManager->getUrl('dashboard/dashboard/mobile', [
                    'token' => $board->getMobileToken(),
                ]),
            ];
        }

        return $data;
    }
}
