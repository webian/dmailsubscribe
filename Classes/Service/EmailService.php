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

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception as ConfigurationException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Email Service
 *
 * Wrapper Service to quickly send emails
 *
 * @package Dmailsubscribe
 * @subpackage Service
 */
class EmailService
{
    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var \DPN\Dmailsubscribe\Service\SettingsService
     * @inject
     */
    protected $settingsService;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * @param \DPN\Dmailsubscribe\Service\SettingsService $settingsService
     * @return void
     */
    public function injectSettingsService(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param string $toEmail
     * @param string $toName
     * @param string $templateName
     * @param boolean $html
     * @param array $variables
     * @throws ConfigurationException
     * @return boolean
     */
    public function send($toEmail, $toName, $templateName, $html = true, array $variables = array())
    {
        $charset = $this->settingsService->getSetting('charset', 'utf-8');
        $subject = $this->settingsService->getSetting('subject', 'Newsletter Subsciption');

        if (null === ($fromEmail = $this->settingsService->getSetting('fromEmail'))) {
            throw new ConfigurationException('Sender email address is not specified.');
        }

        if (null === ($fromName = $this->settingsService->getSetting('fromName'))) {
            throw new ConfigurationException('Sender name is not specified.');
        }

        $htmlView = $this->getView($templateName, 'html');
        $htmlView->assignMultiple($variables);
        $htmlView->assign('charset', $charset);
        $htmlView->assign('title', $subject);
        $htmlBody = $htmlView->render();

        $plainView = $this->getView($templateName, 'txt');
        $plainView->assignMultiple($variables);
        $plainView->assign('charset', $charset);
        $plainView->assign('title', $subject);
        $plainBody = $plainView->render();

        /** @var MailMessage $message */
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message->setTo([$toEmail => $toName])
            ->setFrom([$fromEmail => $fromName])
            ->setSubject($subject)
            ->setCharset($charset);

        if (false === $html) {
            $message->setBody($plainBody, 'text/plain');
        } else {
            $message->setBody($htmlBody, 'text/html');
            $message->addPart($plainBody, 'text/plain');
        }

        $message->send();

        return $message->isSent();
    }

    /**
     * @param string $templateName
     * @param string $format
     * @return StandaloneView
     */
    protected function getView($templateName, $format = 'html')
    {
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setFormat($format);
        $view->getRequest()->setControllerExtensionName('Dmailsubscribe');

        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $templateRootPath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
        $layoutRootPath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['layoutRootPath']);
        $templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . '.' . $format;

        $view->setTemplatePathAndFilename($templatePathAndFilename);
        $view->setLayoutRootPaths([$layoutRootPath]);

        return $view;
    }
}
