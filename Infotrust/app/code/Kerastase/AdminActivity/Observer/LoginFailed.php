<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Kerastase\AdminActivity\Helper\Data as Helper;

/**
 * Class LoginFailed
 * @package Kerastase\AdminActivity\Observer
 */
class LoginFailed implements ObserverInterface
{
    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var \Magento\User\Model\User
     */
    public $user;

    /**
     * @var \Kerastase\AdminActivity\Api\LoginRepositoryInterface
     */
    public $loginRepository;

    /**
     * @var \Kerastase\AdminActivity\Helper\Benchmark
     */
    public $benchmark;

    /**
     * LoginFailed constructor.
     * @param Helper $helper
     * @param \Magento\User\Model\User $user
     * @param \Kerastase\AdminActivity\Api\LoginRepositoryInterface $loginRepository
     * @param \Kerastase\AdminActivity\Helper\Benchmark $benchmark
     */
    public function __construct(
        Helper $helper,
        \Magento\User\Model\User $user,
        \Kerastase\AdminActivity\Api\LoginRepositoryInterface $loginRepository,
        \Kerastase\AdminActivity\Helper\Benchmark $benchmark
    ) {
        $this->helper = $helper;
        $this->user = $user;
        $this->loginRepository = $loginRepository;
        $this->benchmark = $benchmark;
    }

    /**
     * Login failed
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->benchmark->start(__METHOD__);
        if (!$this->helper->isLoginEnable()) {
            return $observer;
        }

        $user = null;
        if ($observer->getUserName()) {
            $user = $this->user->loadByUsername($observer->getUserName());
        }

        $this->loginRepository
            ->setUser($user)
            ->addFailedLog($observer->getException()->getMessage());
        $this->benchmark->end(__METHOD__);
    }
}
