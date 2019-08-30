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


#tx_dmailsubscribe_fe%5Baction%5D=unsubscribe&tx_dmailsubscribe_fe%5BconfirmationCode%5D=bf7a7038&tx_dmailsubscribe_fe%5Bcontroller%5D=Subscription&tx_dmailsubscribe_fe%5BsubscriptionUid%5D=1282&cHash=ff945e417faf1db3f2322c98af7c3b85
# do not generate cHash for newsletter link params
$TYPO3_CONF_VARS['FE']['cacheHash']['excludedParameters'][] = 'tx_dmailsubscribe_fe[confirmationCode]';
$TYPO3_CONF_VARS['FE']['cacheHash']['excludedParameters'][] = 'tx_dmailsubscribe_fe[subscriptionUid]';