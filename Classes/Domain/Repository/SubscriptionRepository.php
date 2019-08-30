<?php

namespace DPN\Dmailsubscribe\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Björn Fromme <fromme@dreipunktnull.come>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use DPN\Dmailsubscribe\Domain\Model\Subscription;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Subscription Repository
 *
 * @package Dmailsubscribe
 * @subpackage Domain\Repository
 */
class SubscriptionRepository extends Repository
{
    /**
     * Fetches a single subscription by provided email address
     *
     * @param string $email
     * @param array $lookupPageIds
     * @return Subscription
     */
    public function findByEmail($email, array $lookupPageIds = array())
    {
        $query = $this->createQuery();

        $query->getQuerySettings()->setIgnoreEnableFields(true);

        if (0 < count($lookupPageIds)) {
            $defaultPageIds = $query->getQuerySettings()->getStoragePageIds();
            // @TODO: Lookup replacement
            $combinedPageIds = $defaultPageIds + $lookupPageIds;
            $query->getQuerySettings()->setStoragePageIds($combinedPageIds);
        }

        $query->matching($query->equals('email', $email));

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }

    /**
     * Fetches a single subscription by provided uid and ignores
     * hidden field which is used to determine confirmation status
     *
     * @param integer $uid
     * @param array $lookupPageIds
     * @return Subscription
     */
    public function findNotConfirmedByUid($uid, array $lookupPageIds = array())
    {
        $query = $this->createQuery();

        $query->getQuerySettings()->setIgnoreEnableFields(true);

        if (0 < count($lookupPageIds)) {
            $defaultPageIds = $query->getQuerySettings()->getStoragePageIds();
            $combinedPageIds = $lookupPageIds + $defaultPageIds;
            $query->getQuerySettings()->setStoragePageIds($combinedPageIds);
        }

        $query->matching($query->logicalAnd(
            $query->equals('deleted', 0),
            $query->equals('uid', (integer) $uid)
        ));

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }
}
