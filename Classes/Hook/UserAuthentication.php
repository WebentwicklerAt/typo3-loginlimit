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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use WebentwicklerAt\Loginlimit\Domain\Model\LoginAttempt;
use WebentwicklerAt\Loginlimit\Domain\Model\Ban;
use WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository;
use WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository;

/**
 * Post user look-up hook for \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
 * to handle logging of login attempts and bans
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class UserAuthentication
{
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
	public function __construct()
    {
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$this->settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('loginlimit');
        $this->persistenceManager = $this->objectManager->get(PersistenceManagerInterface::class);
        $this->loginAttemptRepository = $this->objectManager->get(LoginAttemptRepository::class);
        $this->banRepository = $this->objectManager->get(BanRepository::class);
	}

	/**
	 * Method called by AbstractUserAuthentication
	 *
	 * @param array $params
	 * @return void
	 */
	public function postUserLookUp(&$params)
    {
		if ($this->isLoginlimitActive($params)) {
			$loginData = $params['pObj']->getLoginFormData();
			$ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
			$username = $loginData['uname'];
			$this->logLoginAttempt($ip, $username);

			$loginAttempts = $this->loginAttemptRepository->countLoginAttemptsByIp($ip, $this->settings['findTime']);
			if ($loginAttempts >= (int)$this->settings['maxRetry']) {
				$this->ban($ip, '');
			}

			$loginAttempts = $this->loginAttemptRepository->countLoginAttemptsByUsername($username, $this->settings['findTime']);
			if ($loginAttempts >= (int)$this->settings['maxRetry']) {
				$this->ban('', $username);
			}

			if ($this->settings['delayLogin']) {
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
	protected function isLoginlimitActive(&$params)
    {
		if ($params['pObj']->loginFailure &&
			($params['pObj']->loginType === 'FE' && $this->settings['enableFrontend'] ||
				$params['pObj']->loginType === 'BE' && $this->settings['enableBackend'])
		) {
			return true;
		}

		return false;
	}

	/**
	 * Logs login attempt for IP and username
	 *
	 * @param string $ip
	 * @param string $username
	 * @return void
	 */
	protected function logLoginAttempt($ip, $username)
    {
		$loginAttempt = $this->objectManager->get(LoginAttempt::class);
		$loginAttempt->setIp($ip);
		$loginAttempt->setUsername($username);

        $this->loginAttemptRepository->add($loginAttempt);
        $this->persistenceManager->persistAll();
	}

	/**
	 * Bans IP and username
	 *
	 * @param string $ip
	 * @param string $username
	 * @return void
	 */
	protected function ban($ip, $username)
    {
		$ban = $this->banRepository->findBan($ip, $username);
		if ($ban === null) {
			$ban = $this->objectManager->get(Ban::class);
			$ban->setIp($ip);
			$ban->setUsername($username);
            $this->banRepository->add($ban);
		} else {
			$ban->setTstamp(new \DateTime('@' . $GLOBALS['EXEC_TIME']));
            $this->banRepository->update($ban);
		}

        $this->persistenceManager->persistAll();
	}
}
