<?php
namespace WebentwicklerAt\Loginlimit\Service;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Page\PageRepository;
use WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository;
use WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository;

/**
 * Service avoids authentication after ban
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class AuthenticationService extends AbstractAuthenticationService
{
    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Repository for login attempt
     *
     * @var \WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository
     */
    protected $loginAttemptRepository;

    /**
     * Repository for ban
     *
     * @var \WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository
     */
    protected $banRepository;

    /**
     * Extension manager settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Checks if service is available
     * Instantiate required objects
     *
     * @return boolean
     */
    public function init()
    {
        // in frontend TCA is not loaded
        if (TYPO3_MODE === 'FE') {
            if ($GLOBALS['TCA'] === null) {
                ExtensionManagementUtility::loadBaseTca(false);
            }
            if (!$GLOBALS['TSFE']->sys_page instanceof PageRepository) {
                $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
            }
        }

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('loginlimit');

        if ($this->settings['enableCleanUpAtLogin']) {
            $cleanUpService = $this->objectManager->get(CleanUpService::class);
            $cleanUpService->deleteExpiredEntries();
        }

        return true;
    }

    /**
     * Returns invalid user after ban
     *
     * @return mixed User array or FALSE
     */
    public function getUser()
    {
        if ($this->isLoginlimitActive() && $this->isBanned()) {
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup'][$this->authInfo['loginType'] . '_alwaysAuthUser'] = false;

            return ['uid' => 0];
        }

        return false;
    }

    /**
     * Avoids call of further authentication mechanisms after ban
     *
     * @param array $user Data of user.
     * @return integer >= 200: User authenticated successfully.
     *                         No more checking is needed by other auth services.
     *                 >= 100: User not authenticated; this service is not responsible.
     *                         Other auth services will be asked.
     *                 > 0:    User authenticated successfully.
     *                         Other auth services will still be asked.
     *                 <= 0:   Authentication failed, no more checking needed
     *                         by other auth services.
     */
    public function authUser(array $user)
    {
        $ok = 100;

        if ($this->isLoginlimitActive() && $this->isBanned()) {
            $ok = -1;
        }

        return $ok;
    }

    /**
     * Returns if login limit is active based on login type and settings
     *
     * @return boolean
     */
    protected function isLoginlimitActive()
    {
        if (($this->authInfo['loginType'] === 'FE' && $this->settings['enableFrontend'] ||
            $this->authInfo['loginType'] === 'BE' && $this->settings['enableBackend'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns if IP or username is banned
     *
     * @return boolean
     */
    protected function isBanned()
    {
        $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        $username = $this->login['uname'];

        if ($this->getBanRepository()->findActiveBan($ip, $username, $this->settings['banTime'])) {
            return true;
        }

        return false;
    }
    
    /**
     * Helper to get ban repository
     * Only instantiate object if required
     *
     * @return \WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository
     */
    protected function getBanRepository()
    {
        if (!isset($this->banRepository)) {
            $this->banRepository = $this->objectManager->get(BanRepository::class);
        }

        return $this->banRepository;
    }
}
