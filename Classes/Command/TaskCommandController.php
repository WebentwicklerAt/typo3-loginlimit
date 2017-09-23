<?php
namespace WebentwicklerAt\Loginlimit\Command;

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
 * Extbase CommandController Task
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class TaskCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {
	/**
	 * Clean up expired entries
	 *
	 * Deletes expired login attempts and bans.
	 *
	 * @return void
	 */
	public function cleanUpCommand() {
		$service = $this->objectManager->get('WebentwicklerAt\\Loginlimit\\Service\\CleanUpService');
		$service->deleteExpiredEntries();
	}
}