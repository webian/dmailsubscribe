<?php

namespace DPN\Dmailsubscribe\ViewHelpers;

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

use DPN\Dmailsubscribe\Service\SettingsService;
use TYPO3\CMS\Extbase\Configuration\Exception as ConfigurationException;
use TYPO3\CMS\Fluid\ViewHelpers\Link\ActionViewHelper;

use TYPO3\CMS\Extbase\Annotation\Inject;

/**
 * Class AbstractUriViewHelper
 *
 * Base class for uri-generating ViewHelpers
 *
 * @package Dmailsubscribe
 * @subpackage ViewHelpers
 */
abstract class AbstractUriViewHelper extends ActionViewHelper
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var \DPN\Dmailsubscribe\Service\SettingsService
     * @Inject
     */
    protected $settingsService;

    /**
     * @param \DPN\Dmailsubscribe\Service\SettingsService $settingsService
     * @return void
     */
    public function injectSettingsService(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('subscriptionUid', 'integer', 'Uid of the subscription to confirm.', true);
        $this->registerArgument('confirmationCode', 'string', 'Confirmation code of the subscription to confirm.', true);
    }

    /**
	 * 
     * @throws ConfigurationException
     * @return string
     */
    public function render()
    {
        if (null === ($pluginPageUid = $this->settingsService->getSetting('pluginPageUid'))) {
            throw new ConfigurationException('Plugin page Uid is not configured.');
        }

        $arguments = array(
            'subscriptionUid' => $this->arguments['subscriptionUid'],
            'confirmationCode' => $this->arguments['confirmationCode'],
        );

        $this->arguments['action'] = $this->action;
		$this->arguments['arguments'] = $arguments;
		$this->arguments['controller'] = 'Subscription';
		$this->arguments['extensionName'] = null;
		$this->arguments['pluginName'] = null;
		$this->arguments['pageUid'] = $pluginPageUid;
		$this->arguments['pageType'] = 0;
		$this->arguments['noCache'] = false;
		$this->arguments['noCacheHash'] = true;
		$this->arguments['section'] = '';
		$this->arguments['format'] = '';
		$this->arguments['linkAccessRestrictedPages'] = false;
		$this->arguments['additionalParams'] = [];
		$this->arguments['absolute'] = true;
		

        return parent::render();
    }
}
