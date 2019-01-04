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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository;
use WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository;

/**
 * Service cleans up expired entries
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class CleanUpService implements SingletonInterface
{
    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Extension manager settings
     *
     * @var array
     */
    protected $settings;

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
     * Injects object manager
     *
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Injects persistence manager
     *
     * @param \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager
     * @return void
     */
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Injects repository for login attempt
     *
     * @param \WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository $loginAttemptRepository
     * @return void
     */
    public function injectLoginAttemptRepository(LoginAttemptRepository $loginAttemptRepository)
    {
        $this->loginAttemptRepository = $loginAttemptRepository;
    }

    /**
     * Injects repository for ban
     *
     * @param \WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository $banRepository
     * @return void
     */
    public function injectBanRepository(BanRepository $banRepository)
    {
        $this->banRepository = $banRepository;
    }

    /**
     * Initializes object
     *
     * @return void
     */
    public function initializeObject()
    {
        $this->settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('loginlimit');
    }

    /**
     * Deletes all expired entries
     *
     * @return void
     */
    public function deleteExpiredEntries()
    {
        $this->deleteExpiredLoginAttempts();
        $this->deleteExpiredBans();
        $this->persistenceManager->persistAll();
    }

    /**
     * Deletes expired login attempts
     *
     * @return void
     */
    protected function deleteExpiredLoginAttempts()
    {
        $findtime = $this->settings['findTime'];
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
    protected function deleteExpiredBans()
    {
        $bantime = $this->settings['banTime'];
        if ($bantime >= 0) {
            $expiredEntries = $this->banRepository->findExpired($bantime);
            foreach ($expiredEntries as $expiredEntry) {
                $this->banRepository->remove($expiredEntry);
            }
        }
    }
}
