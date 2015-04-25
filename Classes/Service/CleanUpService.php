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

/**
 * Service cleans up expired entries
 *
 * @author Gernot Leitgab <typo3@webentwickler.at>
 */
class CleanUpService implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * Object manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Injects object manager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Persistence manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects persistence manager
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Repository for login attempt
	 *
	 * @var \WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository
	 */
	protected $loginAttemptRepository;

	/**
	 * Injects repository for login attempt
	 *
	 * @param \WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository $loginAttemptRepository
	 * @return void
	 */
	public function injectLoginAttemptRepository(\WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository $loginAttemptRepository) {
		$this->loginAttemptRepository = $loginAttemptRepository;
	}

	/**
	 * Repository for ban
	 *
	 * @var \WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository
	 */
	protected $banRepository;

	/**
	 * Injects repository for ban
	 *
	 * @param \WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository $banRepository
	 * @return void
	 */
	public function injectBanRepository(\WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository $banRepository) {
		$this->banRepository = $banRepository;
	}

	/**
	 * Extension manager settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Initializes object
	 *
	 * @return void
	 */
	public function initializeObject() {
		$configurationUtility = $this->objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility');
		$this->settings = $configurationUtility->getCurrentConfiguration('loginlimit');
	}

	/**
	 * Deletes all expired entries
	 *
	 * @return void
	 */
	public function deleteExpiredEntries() {
		$this->deleteExpiredLoginAttempts();
		$this->deleteExpiredBans();
		$this->persistenceManager->persistAll();
	}

	/**
	 * Deletes expired login attempts
	 *
	 * @return void
	 */
	protected function deleteExpiredLoginAttempts() {
		$findtime = $this->settings['findtime']['value'];
		$expiredEntries = $this->loginAttemptRepository->findExpired($findtime);
		foreach ($expiredEntries as $expiredEntry) {
			$this->loginAttemptRepository->remove($expiredEntry);
		}
	}

	/**
	 * Deletes expired bans
	 *
	 * @return void
	 */
	protected function deleteExpiredBans() {
		$bantime = $this->settings['bantime']['value'];
		if ($bantime >= 0) {
			$expiredEntries = $this->banRepository->findExpired($bantime);
			foreach ($expiredEntries as $expiredEntry) {
				$this->banRepository->remove($expiredEntry);
			}
		}
	}
}