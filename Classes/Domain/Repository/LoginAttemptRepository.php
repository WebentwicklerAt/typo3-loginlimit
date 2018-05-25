<?php
namespace WebentwicklerAt\Loginlimit\Domain\Repository;

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
 * Repository for \WebentwicklerAt\Loginlimit\Domain\Model\LoginAttempt
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class LoginAttemptRepository extends AbstractRepository
{
	/**
	 * Counts active (not expired) login attempts based on IP
	 *
	 * @param string $ip
	 * @param integer $findtime
	 * @return integer
	 */
	public function countLoginAttemptsByIp($ip, $findtime)
    {
		$query = $this->createQuery();

		$constraints = $query->logicalAnd(
			$query->equals('ip', $ip),
			$query->greaterThanOrEqual('tstamp', $GLOBALS['EXEC_TIME'] - (int)$findtime)
		);

		return $query->matching($constraints)->execute()->count();
	}

	/**
	 * Counts active (not expired) login attempts based on username
	 *
	 * @param string $username
	 * @return integer
	 */
	public function countLoginAttemptsByUsername($username, $findtime)
    {
		$query = $this->createQuery();

		$constraints = $query->logicalAnd(
			$query->equals('username', $username),
			$query->greaterThanOrEqual('tstamp', $GLOBALS['EXEC_TIME'] - (int)$findtime)
		);

		return $query->matching($constraints)->execute()->count();
	}

	/**
	 * Finds expired login attempts
	 *
	 * @param integer $findtime
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findExpired($findtime)
    {
		$query = $this->createQuery();

		$constraints = $query->lessThan('tstamp', $GLOBALS['EXEC_TIME'] - (int)$findtime);

		return $query->matching($constraints)->execute();
	}
}
