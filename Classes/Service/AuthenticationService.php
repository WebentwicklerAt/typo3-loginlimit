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

use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service avoids authentication after ban
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class AuthenticationService extends \TYPO3\CMS\Sv\AbstractAuthenticationService {
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
	public function init() {
		// in frontend TCA is not loaded
		if (TYPO3_MODE === 'FE') {
			if ($GLOBALS['TCA'] === NULL) {
				\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadBaseTca(FALSE);
			}
			if (!$GLOBALS['TSFE']->sys_page instanceof \TYPO3\CMS\Frontend\Page\PageRepository) {
				$GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			}
		}

		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$configurationUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility');
		$this->settings = $configurationUtility->getCurrentConfiguration('loginlimit');

		if ($this->settings['enableCleanUpAtLogin']['value']) {
			$cleanUpService = $this->objectManager->get('WebentwicklerAt\\Loginlimit\\Service\\CleanUpService');
			$cleanUpService->deleteExpiredEntries();
		}

		return TRUE;
	}

	/**
	 * Returns invalid user after ban
	 *
	 * @return mixed User array or FALSE
	 */
	public function getUser() {
		if ($this->isLoginlimitActive() && $this->isBanned()) {
			$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup'][$this->authInfo['loginType'] . '_alwaysAuthUser'] = FALSE;
			return array('uid' => 0);
		}

		return FALSE;
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
	public function authUser(array $user) {
		$OK = 100;

		if ($this->isLoginlimitActive() && $this->isBanned()) {
			$OK = -1;
		}

		return $OK;
	}

	/**
	 * Returns if login limit is active based on login type and settings
	 *
	 * @return boolean
	 */
	protected function isLoginlimitActive() {
		if (($this->authInfo['loginType'] === 'FE' && $this->settings['enableFrontend']['value'] ||
			$this->authInfo['loginType'] === 'BE' && $this->settings['enableBackend']['value'])
		) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Returns if IP or username is banned
	 *
	 * @return boolean
	 */
	protected function isBanned() {
		$ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
		$username = $this->login['uname'];

		if ($this->getBanRepository()->findActiveBan($ip, $username, $this->settings['bantime']['value'])) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Helper to get login attempt repository
	 * Only instantiate object if required
	 *
	 * @return \WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository
	 */
	protected function getLoginAttemptRepository() {
		if (!isset($this->loginAttemptRepository)) {
			$this->loginAttemptRepository = $this->objectManager->get('WebentwicklerAt\\Loginlimit\\Domain\\Repository\\LoginAttemptRepository');
		}

		return $this->loginAttemptRepository;
	}

	/**
	 * Helper to get ban repository
	 * Only instantiate object if required
	 *
	 * @return \WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository
	 */
	protected function getBanRepository() {
		if (!isset($this->banRepository)) {
			$this->banRepository = $this->objectManager->get('WebentwicklerAt\\Loginlimit\\Domain\\Repository\\BanRepository');
		}

		return $this->banRepository;
	}
}