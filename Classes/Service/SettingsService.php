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
 * Settings Service
 *
 * Wrapper Service to simplify fetching of settings defined in
 * the extension's TS
 *
 * @package Dmailsubscribe
 * @subpackage Service
 */
class Tx_Dmailsubscribe_Service_SettingsService {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var array
	 */
	private static $settings;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Returns settings value for provided settings name
	 * or default value if not set. Value can be trimExploded
	 * by provided delimiter.
	 *
	 * @param string $name
	 * @param string $default
	 * @param string $explode
	 * @return mixed
	 * @api
	 */
	public function getSetting($name, $default = NULL, $explode = NULL) {
		if (NULL === self::$settings) {
			self::$settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		}

		$setting = NULL;
		if (TRUE === isset(self::$settings[$name])) {
			if (FALSE === empty(self::$settings[$name])) {
				$setting = self::$settings[$name];
				if (NULL !== $explode) {
					$setting = t3lib_div::trimExplode($explode, $setting);
				}
			} else {
				$setting = $default;
			}
		}

		return $setting;
	}
}
