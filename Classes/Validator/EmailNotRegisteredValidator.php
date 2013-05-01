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
 * Validator: Email must not be registered
 *
 * Returns FALSE as validation result if email is already registered.
 *
 * @package Dmailsubscribe
 * @subpackage Validator
 */
class Tx_Dmailsubscribe_Validator_EmailNotRegisteredValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public function isValid($value) {
		/** @var Tx_Dmailsubscribe_Domain_Repository_SubscriptionRepository $repository */
		$repository = $this->objectManager->get('Tx_Dmailsubscribe_Domain_Repository_SubscriptionRepository');

		$result = $repository->findByEmail($value, $this->options['lookupPageIds']);

		if (NULL !== $result) {
			$this->addError('The given email address is already registered.', 1367223995);
			return FALSE;
		}

		return TRUE;
	}
}
