<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'DPN.Dmailsubscribe',
    'Fe',
    array(
        'Subscription' => 'new, subscribe, confirm, unsubscribe, unsubscribeform, message',
    ),
    array(
        'Subscription' => 'new, subscribe, confirm, unsubscribe, unsubscribeform, message',
    )
);
