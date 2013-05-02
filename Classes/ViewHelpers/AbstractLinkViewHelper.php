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
 * Class Tx_Dmailsubscribe_ViewHelpers_AbstractLinkViewHelper
 *
 * Base class for link-generating ViewHelpers
 *
 * @package Dmailsubscribe
 * @subpackage ViewHelpers
 */
abstract class Tx_Dmailsubscribe_ViewHelpers_AbstractLinkViewHelper extends Tx_Fluid_ViewHelpers_Link_ActionViewHelper {

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var Tx_Dmailsubscribe_Service_SettingsService
	 */
	protected $settingsService;

	/**
	 * @param Tx_Dmailsubscribe_Service_SettingsService $settingsService
	 * @return void
	 */
	public function injectSettingsService(Tx_Dmailsubscribe_Service_SettingsService $settingsService) {
		$this->settingsService = $settingsService;
	}

	/**
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('subscriptionUid', 'integer', 'Uid of the subscription to confirm.', TRUE);
		$this->registerArgument('confirmationCode', 'string', 'Confirmation code of the subscription to confirm.', TRUE);
	}

	/**
	 * @throws Tx_Extbase_Configuration_Exception
	 * @return string
	 */
	public function render() {
		if (NULL === ($pluginPageUid = $this->settingsService->getSetting('pluginPageUid'))) {
			throw new Tx_Extbase_Configuration_Exception('Plugin page Uid is not configured.');
		}

		$arguments = array(
			'subscriptionUid' => $this->arguments['subscriptionUid'],
			'confirmationCode' => $this->arguments['confirmationCode'],
		);

		return parent::render($this->action, $arguments, 'Subscription', NULL, NULL, $pluginPageUid, 0, FALSE, FALSE, '', '', FALSE, array(), TRUE);
	}

}
