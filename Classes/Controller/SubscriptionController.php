<?php
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

/**
 * Subscription Controller
 *
 * Handles the Subscription model object, making new
 * Subscriptions, unsubscribing, confirming etc.
 *
 * @package Dmailsubscribe
 * @subpackage Controller
 */
class Tx_Dmailsubscribe_Controller_SubscriptionController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Dmailsubscribe_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @var Tx_Dmailsubscribe_Domain_Repository_SubscriptionRepository
	 */
	protected $subscriptionRepository;

	/**
	 * @var Tx_Dmailsubscribe_Service_SettingsService
	 */
	protected $settingsService;

	/**
	 * @param Tx_Dmailsubscribe_Domain_Repository_CategoryRepository $repository
	 * @return void
	 */
	public function injectCategoryRepository(Tx_Dmailsubscribe_Domain_Repository_CategoryRepository $repository) {
		$this->categoryRepository = $repository;
	}

	/**
	 * @param Tx_Dmailsubscribe_Domain_Repository_SubscriptionRepository $repository
	 * @return void
	 */
	public function injectSubscriptionRepository(Tx_Dmailsubscribe_Domain_Repository_SubscriptionRepository $repository) {
		$this->subscriptionRepository = $repository;
	}

	/**
	 * @param Tx_Dmailsubscribe_Service_SettingsService $settingsService
	 * @return void
	 */
	public function injectSettingsService(Tx_Dmailsubscribe_Service_SettingsService $settingsService) {
		$this->settingsService = $settingsService;
	}

	/**
	 * @param Tx_Dmailsubscribe_Domain_Model_Subscription $subscription
	 * @return void
	 * @ignorevalidation $subscription
	 */
	public function newAction(Tx_Dmailsubscribe_Domain_Model_Subscription $subscription = NULL) {
		if (NULL !== t3lib_div::_GET('a') && NULL !== t3lib_div::_GET('c') && NULL !== t3lib_div::_GET('u')) {
			$action = t3lib_div::_GET('a');
			if ('confirm' == $action || 'unsubscribe' == $action) {
				$arguments = array(
					'confirmationCode' => t3lib_div::_GET('c'),
					'subscriptionUid'  => t3lib_div::_GET('u'),
				);
				$this->redirect($action, NULL, NULL, $arguments);
			}
		}

		$categoryPids     = $this->settingsService->getSetting('categoryPids', array(), ',');
		$additionalFields = array_fill_keys($this->settingsService->getSetting('additionalFields', array(), ','), TRUE);
		$requiredFields   = array_fill_keys($this->settingsService->getSetting('requiredFields', array(), ','), TRUE);

		$selectedCategories = array();
		if (NULL === ($originalRequest = $this->request->getOriginalRequest())) {
			$subscription = $this->objectManager->create('Tx_Dmailsubscribe_Domain_Model_Subscription');
		} else {
			$subscription = $originalRequest->getArgument('subscription');
			if ($originalRequest->hasArgument('categories')) {
				$selectedCategories = $originalRequest->getArgument('categories');
			}
		}

		$selectableCategories = $this->categoryRepository->findAllInPids($categoryPids);
		$formCategories  = array();

		foreach ($selectableCategories as $category) {
			$formCategories[] = array(
				'uid'     => $category->getUid(),
				'title'   => $category->getTitle(),
				'checked' => (boolean) $selectedCategories[$category->getUid()],
			);
		}

		$this->view->assign('subscription',     $subscription);
		$this->view->assign('additionalFields', $additionalFields);
		$this->view->assign('requiredFields',   $requiredFields);
		$this->view->assign('categories',       $formCategories);
	}

	/**
	 * @param Tx_Dmailsubscribe_Domain_Model_Subscription $subscription
	 * @param array $categories
	 * @return void
	 * @validate $subscription Tx_Dmailsubscribe_Validation_Validator_SubscriptionValidator
	 */
	public function subscribeAction(Tx_Dmailsubscribe_Domain_Model_Subscription $subscription, $categories = array()) {
		$categoryPids = $this->settingsService->getSetting('categoryPids', array(), ',');

		$selectedCategories = $this->categoryRepository->findAllByUids($categories, $categoryPids);
		foreach ($selectedCategories as $category) {
			$subscription->addCategory($category);
		}

		$this->subscriptionRepository->add($subscription);

		/** @var Tx_Extbase_Persistence_Manager $persistenceManager */
		$persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		$persistenceManager->persistAll();

		$templateVariables = array(
			'subscription'     => $subscription,
			'confirmationCode' => $this->generateConfirmationCode($subscription->getUid()),
		);

		/** @var Tx_Dmailsubscribe_Service_EmailService $emailService */
		$emailService = $this->objectManager->get('Tx_Dmailsubscribe_Service_EmailService');
		$emailService->send($subscription->getEmail(), $subscription->getName(), 'NewSubscription', $subscription->getReceiveHtml(), $templateVariables);

		$message = Tx_Extbase_Utility_Localization::translate('message.subscribe.success', $this->extensionName);
		$this->flashMessageContainer->add($message);

		$this->redirect('new');
	}

	/**
	 * @param string $subscriptionUid
	 * @param string $confirmationCode
	 * @return void
	 */
	public function confirmAction($subscriptionUid, $confirmationCode) {
		$muteConfirmationErrors = (boolean) $this->settingsService->getSetting('muteConfigurationErrors', TRUE);

		if (FALSE === ($confirmationCodeValid = $this->validateConfirmationCode($subscriptionUid, $confirmationCode))) {
			if (FALSE === $muteConfirmationErrors) {
				$message = Tx_Extbase_Utility_Localization::translate('message.confirm.confirmation_code_invalid', $this->extensionName);
				$this->flashMessageContainer->add($message);
			}
		}

		/** @var Tx_Dmailsubscribe_Domain_Model_Subscription $subscription */
		if (NULL === ($subscription = $this->subscriptionRepository->findNotConfirmedByUid($subscriptionUid))) {
			if (FALSE === $muteConfirmationErrors) {
				$message = Tx_Extbase_Utility_Localization::translate('message.confirm.subscription_not_found', $this->extensionName);
				$this->flashMessageContainer->add($message);
			}
		}

		if (TRUE === $confirmationCodeValid && NULL !== $subscription && TRUE === $subscription->getHidden()) {
			$subscription->setHidden(FALSE);
			$this->subscriptionRepository->update($subscription);
			$message = Tx_Extbase_Utility_Localization::translate('message.confirm.success', $this->extensionName);
			$this->flashMessageContainer->add($message);
		}

		$this->redirect('new');
	}

	/**
	 * @param integer $subscriptionUid
	 * @param string $confirmationCode
	 * @return void
	 */
	public function unsubscribeAction($subscriptionUid, $confirmationCode) {
		$muteUnsubscribeErrors = (boolean) $this->settingsService->getSetting('muteUnsubscribeErrors', TRUE);

		if (FALSE === ($confirmationCodeValid = $this->validateConfirmationCode($subscriptionUid, $confirmationCode))) {
			if (FALSE === $muteUnsubscribeErrors) {
				$message = Tx_Extbase_Utility_Localization::translate('message.unsubscribe.confirmation_code_invalid', $this->extensionName);
				$this->flashMessageContainer->add($message);
			}
		}

		/** @var Tx_Dmailsubscribe_Domain_Model_Subscription $subscription */
		if (NULL === ($subscription = $this->subscriptionRepository->findByUid($subscriptionUid))) {
			if (FALSE === $muteUnsubscribeErrors) {
				$message = Tx_Extbase_Utility_Localization::translate('message.unsubscribe.subscription_not_found', $this->extensionName);
				$this->flashMessageContainer->add($message);
			}
		}

		if (TRUE === $confirmationCodeValid && NULL !== $subscription) {
			$this->subscriptionRepository->remove($subscription);
			$message = Tx_Extbase_Utility_Localization::translate('message.unsubscribe.success', $this->extensionName);
			$this->flashMessageContainer->add($message);
		}

		$this->redirect('new');
	}

	/**
	 * @param integer $uid
	 * @return string
	 */
	private function generateConfirmationCode($uid) {
		return t3lib_div::stdAuthCode($uid, '', 16);
	}

	/**
	 * @param integer $uid
	 * @param string $confirmationCode
	 * @return boolean
	 */
	private function validateConfirmationCode($uid, $confirmationCode) {
		$confirmationCodeForUid = $this->generateConfirmationCode($uid);
		return $confirmationCodeForUid === $confirmationCode;
	}

	/**
	 * Override parent method to disable output of
	 * controller errors as flash messages
	 *
	 * @return boolean
	 */
	public function getErrorFlashMessage() {
		return FALSE;
	}
}
