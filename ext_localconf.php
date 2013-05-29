<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Fe',
	array(
		'Subscription' => 'new, subscribe, confirm, unsubscribe, unsubscribeform, message',
	),
	array(
		'Subscription' => 'new, subscribe, confirm, unsubscribe, unsubscribeform, message',
	)
);
