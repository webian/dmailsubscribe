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

/**
 * Class AbstractLinkViewHelper
 *
 * Base class for link-generating ViewHelpers
 *
 * @package Dmailsubscribe
 * @subpackage ViewHelpers
 */
abstract class AbstractLinkViewHelper extends ActionViewHelper
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var \DPN\Dmailsubscribe\Service\SettingsService
     * @inject
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
     * @param string $action Target action
     * @param array $arguments Arguments
     * @param string $controller Target controller. If NULL current controllerName is used
     * @param string $extensionName Target Extension Name (without "tx_" prefix and no underscores). If NULL the current extension name is used
     * @param string $pluginName Target plugin. If empty, the current plugin name is used
     * @param int $pageUid target page. See TypoLink destination
     * @param int $pageType type of the target page. See typolink.parameter
     * @param bool $noCache set this to disable caching for the target page. You should not need this.
     * @param bool $noCacheHash set this to suppress the cHash query parameter created by TypoLink. You should not need this.
     * @param string $section the anchor to be added to the URI
     * @param string $format The requested format, e.g. ".html
     * @param bool $linkAccessRestrictedPages If set, links pointing to access restricted pages will still link to the page even though the page cannot be accessed.
     * @param array $additionalParams additional query parameters that won't be prefixed like $arguments (overrule $arguments)
     * @param bool $absolute If set, the URI of the rendered link is absolute
     * @param bool $addQueryString If set, the current query parameters will be kept in the URI
     * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the URI. Only active if $addQueryString = TRUE
     * @param string $addQueryStringMethod Set which parameters will be kept. Only active if $addQueryString = TRUE
     * @throws ConfigurationException
     * @return string
     */
    public function render($action = null, array $arguments = [], $controller = null, $extensionName = null, $pluginName = null, $pageUid = null, $pageType = 0, $noCache = false, $noCacheHash = false, $section = '', $format = '', $linkAccessRestrictedPages = false, array $additionalParams = [], $absolute = false, $addQueryString = false, array $argumentsToBeExcludedFromQueryString = [], $addQueryStringMethod = null)
    {
        if (null === ($pluginPageUid = $this->settingsService->getSetting('pluginPageUid'))) {
            throw new ConfigurationException('Plugin page Uid is not configured.');
        }

        $arguments = [
            'subscriptionUid' => $this->arguments['subscriptionUid'],
            'confirmationCode' => $this->arguments['confirmationCode'],
        ];

        return parent::render($this->action, $arguments, 'Subscription', null, null, $pluginPageUid, 0, false, true, '', '', false, [], true);
    }
}
