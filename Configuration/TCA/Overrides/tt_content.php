<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'dmailsubscribe',
    'Fe',
    'Newsletter Subscription'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['dmailsubscribe_fe'] = 'recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['dmailsubscribe_fe'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'dmailsubscribe_fe',
    'FILE:EXT:dmailsubscribe/Configuration/FlexForms/flexform_dmailsubscribe.xml'
);

/***************
 * Default TypoScript
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'dmailsubscribe',
    'Configuration/TypoScript',
    'DirectMail Subscription'
);
