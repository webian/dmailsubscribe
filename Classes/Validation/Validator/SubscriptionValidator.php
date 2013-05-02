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
 * Validator: Subscription object validation wrapper
 *
 * Validates an entire Subscription instance using various other
 * Validators as configured in TypoScript.
 *
 * @package Dmailsubscribe
 * @subpackage Validation/Validator
 */
class Tx_Dmailsubscribe_Validation_Validator_SubscriptionValidator extends Tx_Extbase_Validation_Validator_GenericObjectValidator {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Overrides parent::validate() to add notEmptyValidators
	 * for all required fields and emailNotRegisteredValidator
	 * to email field
	 *
	 * @param mixed $object
	 * @throws Tx_Extbase_Validation_Exception_InvalidSubject
	 * @return Tx_Extbase_Error_Result
	 * @api
	 */
	public function validate($object) {
		if (FALSE === $this->canValidate($object)) {
			throw new Tx_Extbase_Validation_Exception_InvalidSubject(sprintf('Expected "%s" but was "%s"', 'Tx_Dmailsubscribe_Domain_Model_Subscription', get_class($object)));
		}

		$settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

		$requiredFields = array();
		if (TRUE === isset($settings['requiredFields']) && '' !== $settings['requiredFields']) {
			$requiredFields  = t3lib_div::trimExplode(',', $settings['requiredFields']);
		}

		$lookupPageIds = array();
		if (TRUE === isset($settings['lookupPids']) && '' !== $settings['lookupPids']) {
			$lookupPageIds = t3lib_div::trimExplode(',', $settings['lookupPids']);
		}

		$emailNotRegisteredValidator = $this->objectManager->get('Tx_Dmailsubscribe_Validation_Validator_EmailNotRegisteredValidator', array('lookupPageIds' => $lookupPageIds));
		$this->addPropertyValidator('email', $emailNotRegisteredValidator);

		foreach ($requiredFields as $field) {
			$notEmptyValidator = $this->objectManager->get('Tx_Extbase_Validation_Validator_NotEmptyValidator');
			$this->addPropertyValidator($field, $notEmptyValidator);
		}

		return parent::validate($object);
	}

	/**
	 * @param object $object
	 * @return boolean
	 */
	public function canValidate($object) {
		return $object instanceof Tx_Dmailsubscribe_Domain_Model_Subscription;
	}

}
