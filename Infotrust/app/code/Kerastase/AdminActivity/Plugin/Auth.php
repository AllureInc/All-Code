<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Plugin;

use \Kerastase\AdminActivity\Helper\Data as Helper;

/**
 * Class Auth
 * @package Kerastase\AdminActivity\Plugin
 */
class Auth
{
    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var \Kerastase\AdminActivity\Api\LoginRepositoryInterface
     */
    public $loginRepository;

    /**
     * @var \Kerastase\AdminActivity\Helper\Benchmark
     */
    public $benchmark;

    /**
     * Auth constructor.
     * @param Helper $helper
     * @param \Kerastase\AdminActivity\Api\LoginRepositoryInterface $loginRepository
     * @param \Kerastase\AdminActivity\Helper\Benchmark $benchmark
     */
    public function __construct(
        Helper $helper,
        \Kerastase\AdminActivity\Api\LoginRepositoryInterface $loginRepository,
        \Kerastase\AdminActivity\Helper\Benchmark $benchmark
    ) {
        $this->helper = $helper;
        $this->loginRepository = $loginRepository;
        $this->benchmark = $benchmark;
    }

    /**
     * Track admin logout activity
     * @param \Magento\Backend\Model\Auth $auth
     * @param callable $proceed
     * @return mixed
     */
    public function aroundLogout(\Magento\Backend\Model\Auth $auth, callable $proceed)
    {
        $this->benchmark->start(__METHOD__);
        if ($this->helper->isLoginEnable()) {
            $user = $auth->getAuthStorage()->getUser();
            $this->loginRepository->setUser($user)->addLogoutLog();
        }
        $result = $proceed();
        $this->benchmark->end(__METHOD__);
        return $result;
    }
}
