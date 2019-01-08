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
 * Repository for ban records
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class BanRepository extends AbstractRepository
{
    /**
     * @var string
     */
    protected $table = 'tx_loginlimit_ban';

    /**
     * @return string
     */
    protected function getTable(): string
    {
        return $this->table;
    }

    /**
     * Finds first ban based on IP or username
     *
     * @param string $ip
     * @param string $username
     * @return array
     */
    public function findBan(string $ip, $username): array
    {
        $table = $this->getTable();
        $queryBuilder = $this->instantiateQueryBuilderForTable($table);

        $statement = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('ip', $queryBuilder->createNamedParameter($ip)),
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username))
            )
            ->setMaxResults(1)
            ->execute();

        $result = $statement->fetch();
        return $result ?: [];
    }

    /**
     * Finds first active (not expired) ban based on IP or username
     *
     * @param string $ip
     * @param string $username
     * @param int $bantime
     * @return array
     */
    public function findActiveBan(string $ip, string $username, int $bantime): array
    {
        $table = $this->getTable();
        $queryBuilder = $this->instantiateQueryBuilderForTable($table);

        $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('ip', $queryBuilder->createNamedParameter($ip)),
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username))
            );

        if ($bantime >= 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->gte('tstamp', $queryBuilder->createNamedParameter($GLOBALS['EXEC_TIME'] - (int)$bantime, \PDO::PARAM_INT))
            );
        }

        $statement = $queryBuilder
            ->setMaxResults(1)
            ->execute();

        $result = $statement->fetch();
        return $result ?: [];
    }

    /**
     * @param string $ip
     * @param string $username
     */
    public function addBan(string $ip, string $username)
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

    /**
     * @param int $uid
     */
    public function updateBanTime(int $uid)
    {
        $table = $this->getTable();
        $queryBuilder = $this->instantiateQueryBuilderForTable($table);

        $queryBuilder
            ->update($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->set('tstamp', $GLOBALS['EXEC_TIME'])
            ->execute();
    }
}
