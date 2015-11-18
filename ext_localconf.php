<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    $_EXTKEY,
    'Fe',
    array(
        'Subscription' => 'new, subscribe, confirm, unsubscribe, unsubscribeform, message',
    ),
    array(
        'Subscription' => 'new, subscribe, confirm, unsubscribe, unsubscribeform, message',
    )
);
