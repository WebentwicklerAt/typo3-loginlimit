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
 * Repository for \WebentwicklerAt\Loginlimit\Domain\Model\Ban
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class BanRepository extends AbstractRepository
{
    /**
     * Finds first ban based on IP or username
     *
     * @param string $ip
     * @param string $username
     * @return object
     */
    public function findBan($ip, $username)
    {
        $query = $this->createQuery();

        $constraints = $query->logicalOr(
            $query->equals('ip', $ip),
            $query->equals('username', $username)
        );

        $result = $query->matching($constraints)->setLimit(1)->execute();
        return $result->getFirst();
    }

    /**
     * Finds first active (not expired) ban based on IP or username
     *
     * @param string $ip
     * @param string $username
     * @param integer $bantime
     * @return object
     */
    public function findActiveBan($ip, $username, $bantime)
    {
        $query = $this->createQuery();

        $constraints = $query->logicalOr(
            $query->equals('ip', $ip),
            $query->equals('username', $username)
        );
        if ($bantime >= 0) {
            $constraints = $query->logicalAnd(
                $constraints,
                $query->greaterThanOrEqual('tstamp', $GLOBALS['EXEC_TIME'] - (int)$bantime)
            );
        }

        $result = $query->matching($constraints)->setLimit(1)->execute();
        return $result->getFirst();
    }

    /**
     * Finds expired bans
     *
     * @param integer $bantime
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findExpired($bantime)
    {
        $result = [];
        if ($bantime >= 0) {
            $query = $this->createQuery();

            $constraints = $query->lessThan('tstamp', $GLOBALS['EXEC_TIME'] - (int)$bantime);

            $result = $query->matching($constraints)->execute();
        }

        return $result;
    }
}
