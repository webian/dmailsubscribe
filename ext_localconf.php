<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Fe',
	array(
		'Subscription' => 'new, subscribe, confirm, unsubscribe, message',
	),
	array(
		'Subscription' => 'new, subscribe, confirm, unsubscribe, message',
	)
);
