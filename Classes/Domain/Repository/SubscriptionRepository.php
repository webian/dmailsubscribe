<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Björn Fromme <fromme@dreipunktnull.come>
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

/**
 * Subscription Repository
 *
 * @package Dmailsubscribe
 * @subpackage Domain\Repository
 */
class Tx_Dmailsubscribe_Domain_Repository_SubscriptionRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * Fetches a single subscription by provided email address
	 *
	 * @param string $email
	 * @param array $lookupPageIds
	 * @return NULL|Tx_Dmailsubscribe_Doman_Model_Subscription
	 */
	public function findByEmail($email, array $lookupPageIds = array()) {
		$query = $this->createQuery();

		if (0 < count($lookupPageIds)) {
			$defaultPageIds = $query->getQuerySettings()->getStoragePageIds();
			$combinedPageIds = t3lib_div::array_merge($defaultPageIds, $lookupPageIds);
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
	 * @return NULL|Tx_Dmailsubscribe_Doman_Model_Subscription
	 */
	public function findNotConfirmedByUid($uid, array $lookupPageIds = array()) {
		$query = $this->createQuery();

		$query->getQuerySettings()->setRespectEnableFields(FALSE);

		if (0 < count($lookupPageIds)) {
			$defaultPageIds = $query->getQuerySettings()->getStoragePageIds();
			$combinedPageIds = t3lib_div::array_merge($defaultPageIds, $lookupPageIds);
			$query->getQuerySettings()->setStoragePageIds($combinedPageIds);
		}

		$query->matching($query->logicalAnd(
			$query->equals('deleted',0),
			$query->equals('uid', intval($uid))
		));

		$query->setLimit(1);

		return $query->execute()->getFirst();
	}
}
