<?php
declare(strict_types=1);
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
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use WebentwicklerAt\Loginlimit\Domain\Repository\BanRepository;
use WebentwicklerAt\Loginlimit\Domain\Repository\LoginAttemptRepository;

/**
 * Service cleans up expired entries
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class CleanUpService
{
    /**
     * Extension manager settings
     *
     * @var array
     */
    protected $settings;

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
    }

    /**
     * Deletes expired login attempts
     *
     * @return void
     */
    protected function deleteExpiredLoginAttempts()
    {
        $findtime = (int)$this->settings['findTime'];
        $this->loginAttemptRepository->deleteExpired($findtime);
    }

    /**
     * Deletes expired bans
     *
     * @return void
     */
    protected function deleteExpiredBans()
    {
        $bantime = (int)$this->settings['banTime'];
        if ($bantime >= 0) {
            $this->banRepository->deleteExpired($bantime);
        }
    }
}
