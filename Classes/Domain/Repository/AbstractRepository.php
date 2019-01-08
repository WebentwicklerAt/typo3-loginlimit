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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Abstract repository
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
abstract class AbstractRepository
{
    /**
     * @param string $table
     * @return QueryBuilder
     */
    protected function instantiateQueryBuilderForTable(string $table): QueryBuilder
    {
        /** @var \TYPO3\CMS\Core\Database\Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table);

        return $connection->createQueryBuilder();
    }

    /**
     * @return string
     */
    abstract protected function getTable(): string;

    /**
     * Delete expired records (bans / login attempts)
     *
     * @param int $time
     * @return void
     */
    public function deleteExpired(int $time)
    {
        $table = $this->getTable();
        $queryBuilder = $this->instantiateQueryBuilderForTable($table);

        $queryBuilder
            ->delete($table)
            ->where(
                $queryBuilder->expr()->lt('tstamp', $queryBuilder->createNamedParameter($GLOBALS['EXEC_TIME'] - (int)$time, \PDO::PARAM_INT))
            )
            ->execute();
    }
}
