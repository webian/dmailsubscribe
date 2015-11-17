<?php

namespace DPN\Dmailsubscribe\Service;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Settings Service
 *
 * Wrapper Service to simplify fetching of settings defined in
 * the extension's TS
 *
 * @package Dmailsubscribe
 * @subpackage Service
 */
class SettingsService
{
    /**
     * @var array
     */
    private static $settings;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
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
    public function getSetting($name, $default = null, $explode = null)
    {
        if (null === self::$settings) {
            self::$settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
        }

        $setting = null;

        if (true === isset(self::$settings[$name])) {
            if ('' !== self::$settings[$name]) {
                $setting = self::$settings[$name];
                if (null !== $explode) {
                    $setting = GeneralUtility::trimExplode($explode, $setting);
                }
            } else {
                $setting = $default;
            }
        }

        return $setting;
    }
}
