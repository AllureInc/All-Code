<?php
/**
 * Module: Cor_Productmanagement
 * Backend Plugin
 * Loads product grid on the basis of cor_artist attribute filter.
 */
namespace Cor\Productmanagement\Plugin;

class ProductDataProvider
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $coreResource;

    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    protected $adminUsers;

    /**
     * ProductDataProvider constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Magento\User\Model\ResourceModel\User\Collection $adminUsers
    ) {
        $this->logger = $logger;
        $this->authSession = $authSession;
        $this->coreResource = $coreResource;
        $this->adminUsers = $adminUsers;
    }

    /**
     * Loads product collection on the basis of artists.
     */
    public function aroundGetData(
        \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject,
        \Closure $proceed
    ) {
        $isArtistLoggedIn = $this->checkArtistLoggedIn();
        if ($isArtistLoggedIn) {
            $user = $this->getCurrentUserId();
            if (!$subject->getCollection()->isLoaded() && $user) {
                $subject->getCollection()->addFieldToFilter('cor_artist', array('eq' => $user));
                $subject->getCollection()->load();
            }
        }
        return $proceed();
    }

    /**
     * checks if the login user is artist.
     */
    public function checkArtistLoggedIn() {
        $currentUser = $this->getCurrentUser();
        $isArtist = $this->checkArtistRole($currentUser);
        return $isArtist;
    }

    /**
     * if logged in user is not Artist, returns false.
     */
    public function checkArtistRole($user) {
        if ($user->getRole()->getRoleName() == 'Artist') {
            return true;
        }
        return false;
    }

    /**
     * retrieves currently logged in user id.
     */
    public function getCurrentUserId() {
        return $this->getCurrentUser()->getUserId();
    }

    /**
     * retrieves currently logged in user details.
     */
    public function getCurrentUser() {
        return $this->authSession->getUser();
    }
}