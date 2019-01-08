<?php
declare(strict_types=1);
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
     * @var string
     */
    protected $table = 'tx_loginlimit_loginattempt';

    /**
     * @return string
     */
    protected function getTable(): string
    {
        return $this->table;
    }

    /**
     * Counts active (not expired) login attempts based on IP
     *
     * @param string $ip
     * @param int $findtime
     * @return int
     */
    public function countLoginAttemptsByIp(string $ip, int $findtime): int
    {
        $table = $this->getTable();
        $queryBuilder = $this->instantiateQueryBuilderForTable($table);

        $count = $queryBuilder
            ->count('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('ip', $queryBuilder->createNamedParameter($ip)),
                $queryBuilder->expr()->gte('tstamp', $queryBuilder->createNamedParameter($GLOBALS['EXEC_TIME'] - $findtime))
            )
            ->execute()
            ->fetchColumn(0);

        return (int)$count;
    }

    /**
     * Counts active (not expired) login attempts based on username
     *
     * @param string $username
     * @param int $findtime
     * @return int
     */
    public function countLoginAttemptsByUsername(string $username, int $findtime): int
    {
        $table = $this->getTable();
        $queryBuilder = $this->instantiateQueryBuilderForTable($table);

        $count = $queryBuilder
            ->count('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)),
                $queryBuilder->expr()->gte('tstamp', $queryBuilder->createNamedParameter($GLOBALS['EXEC_TIME'] - $findtime))
            )
            ->execute()
            ->fetchColumn(0);

        return (int)$count;
    }

    /**
     * @param string $ip
     * @param string $username
     */
    public function addLogLoginAttempt(string $ip, string $username)
    {
        $table = $this->getTable();
        $queryBuilder = $this->instantiateQueryBuilderForTable($table);

        $queryBuilder
            ->insert($table)
            ->values(
                [
                    'pid' => 0,
                    'tstamp' => $GLOBALS['EXEC_TIME'],
                    'ip' => $ip,
                    'username' => $username,
                ]
            )
            ->execute();
    }
}
