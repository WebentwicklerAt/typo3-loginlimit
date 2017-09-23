<?php
namespace WebentwicklerAt\Loginlimit\Hook;

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
 * Post user look-up hook for \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
 * to handle logging of login attempts and bans
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class UserAuthentication {
	/**
	 * Object manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Persistence manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

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
	 * Constructor
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$configurationUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility');
		$this->settings = $configurationUtility->getCurrentConfiguration('loginlimit');
	}

	/**
	 * Method called by AbstractUserAuthentication
	 *
	 * @param array $params
	 * @return void
	 */
	public function postUserLookUp(&$params) {
		if ($this->isLoginlimitActive($params)) {
			$loginData = $params['pObj']->getLoginFormData();
			$ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
			$username = $loginData['uname'];
			$this->logLoginAttempt($ip, $username);

			$loginAttempts = $this->getLoginAttemptRepository()->countLoginAttemptsByIp($ip, $this->settings['findtime']['value']);
			if ($loginAttempts >= $this->settings['maxretry']['value']) {
				$this->ban($ip, '');
			}

			$loginAttempts = $this->getLoginAttemptRepository()->countLoginAttemptsByUsername($username, $this->settings['findtime']['value']);
			if ($loginAttempts >= $this->settings['maxretry']['value']) {
				$this->ban('', $username);
			}

			if ($this->settings['delayLogin']['value']) {
				sleep(min($loginAttempts, 10));
			}
		}
	}

	/**
	 * Returns if login limit is active based on login type and settings
	 *
	 * @param array $params
	 * @return boolean
	 */
	protected function isLoginlimitActive(&$params) {
		if ($params['pObj']->loginFailure &&
			($params['pObj']->loginType === 'FE' && $this->settings['enableFrontend']['value'] ||
				$params['pObj']->loginType === 'BE' && $this->settings['enableBackend']['value'])
		) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Logs login attempt for IP and username
	 *
	 * @param string $ip
	 * @param string $username
	 * @return void
	 */
	protected function logLoginAttempt($ip, $username) {
		$loginAttempt = $this->objectManager->get('WebentwicklerAt\\Loginlimit\\Domain\\Model\\LoginAttempt');
		$loginAttempt->setIp($ip);
		$loginAttempt->setUsername($username);

		$this->getLoginAttemptRepository()->add($loginAttempt);
		$this->getPersistenceManager()->persistAll();
	}

	/**
	 * Bans IP and username
	 *
	 * @param string $ip
	 * @param string $username
	 * @return void
	 */
	protected function ban($ip, $username) {
		$ban = $this->getBanRepository()->findBan($ip, $username);
		if ($ban === NULL) {
			$ban = $this->objectManager->get('WebentwicklerAt\\Loginlimit\\Domain\\Model\\Ban');
			$ban->setIp($ip);
			$ban->setUsername($username);

			$this->getBanRepository()->add($ban);
		}
		else {
			$ban->setTstamp(new \DateTime('@' . $GLOBALS['EXEC_TIME']));
			$this->getBanRepository()->update($ban);
		}

		$this->getPersistenceManager()->persistAll();
	}

	/**
	 * Helper to get persistence manager
	 * Only instantiate if required
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 */
	protected function getPersistenceManager() {
		if (!isset($this->persistenceManager)) {
			$this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\PersistenceManagerInterface');
		}

		return $this->persistenceManager;
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