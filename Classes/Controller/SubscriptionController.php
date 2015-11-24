<?php

namespace DPN\Dmailsubscribe\Controller;

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

use DPN\Dmailsubscribe\Domain\Model\Category;
use DPN\Dmailsubscribe\Domain\Model\Subscription;
use DPN\Dmailsubscribe\Domain\Repository\CategoryRepository;
use DPN\Dmailsubscribe\Domain\Repository\SubscriptionRepository;
use DPN\Dmailsubscribe\Service\EmailService;
use DPN\Dmailsubscribe\Service\SettingsService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Subscription Controller
 *
 * Handles the Subscription model object, making new
 * Subscriptions, unsubscribing, confirming etc.
 *
 * @package Dmailsubscribe
 * @subpackage Controller
 */
class SubscriptionController extends ActionController
{
    /**
     * @var \DPN\Dmailsubscribe\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoryRepository;

    /**
     * @var \DPN\Dmailsubscribe\Domain\Repository\SubscriptionRepository
     * @inject
     */
    protected $subscriptionRepository;

    /**
     * @var \DPN\Dmailsubscribe\Service\SettingsService
     * @inject
     */
    protected $settingsService;

    /**
     * @param \DPN\Dmailsubscribe\Domain\Repository\CategoryRepository $repository
     * @return void
     */
    public function injectCategoryRepository(CategoryRepository $repository)
    {
        $this->categoryRepository = $repository;
    }

    /**
     * @param \DPN\Dmailsubscribe\Domain\Repository\SubscriptionRepository $repository
     * @return void
     */
    public function injectSubscriptionRepository(SubscriptionRepository $repository)
    {
        $this->subscriptionRepository = $repository;
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
     * This default action for rendering the subscription form will also catch
     * simplified links of the pattern
     *
     * index.php?id=PID&a=ACTION&c=###SYS_AUTHCODE###&u=###USER_uid###
     *
     * to achieve short links to insert into direct_mail newsletters with
     * action being either 'confirm' or 'unsubscribe' and PID being the
     * id of the page including this plugin.
     *
     * @param Subscription $subscription
     * @return void
     * @ignorevalidation $subscription
     */
    public function newAction(Subscription $subscription = null)
    {
        if (null !== GeneralUtility::_GET('a') && null !== GeneralUtility::_GET('c') && null !== GeneralUtility::_GET('u')) {
            $action = GeneralUtility::_GET('a');
            if ('confirm' == $action || 'unsubscribe' == $action) {
                $arguments = array(
                    'confirmationCode' => GeneralUtility::_GET('c'),
                    'subscriptionUid' => GeneralUtility::_GET('u'),
                );
                $this->redirect($action, null, null, $arguments);
            }
        }

        $categoryPids = $this->settingsService->getSetting('categoryPids', array(), ',');
        $additionalFields = array_fill_keys($this->settingsService->getSetting('additionalFields', [], ','), true);
        $requiredFields = array_fill_keys($this->settingsService->getSetting('requiredFields', [], ','), true);

        $selectedCategories = array();
        if (null === ($originalRequest = $this->request->getOriginalRequest())) {
            $subscription = $this->objectManager->get(Subscription::class);
        } else {
            $subscription = $originalRequest->getArgument('subscription');
            if ($originalRequest->hasArgument('categories')) {
                $selectedCategories = $originalRequest->getArgument('categories');
            }
        }

        $selectableCategories = $this->categoryRepository->findAllInPids($categoryPids);
        $formCategories = array();

        /** @var Category $category */
        foreach ($selectableCategories as $category) {
            $formCategories[] = array(
                'uid' => $category->getUid(),
                'title' => $category->getTitle(),
                'checked' => (boolean)$selectedCategories[$category->getUid()],
            );
        }

        $pluginPageUid = $this->settingsService->getSetting('pluginPageUid');
        $unsubscribePageUid = $this->settingsService->getSetting('unsubscribePageUid', $pluginPageUid);

        $this->view->assign('subscription', $subscription);
        $this->view->assign('additionalFields', $additionalFields);
        $this->view->assign('requiredFields', $requiredFields);
        $this->view->assign('categories', $formCategories);
        $this->view->assign('pluginPageUid', $pluginPageUid);
        $this->view->assign('unsubscribePageUid', $unsubscribePageUid);
    }

    /**
     * @param Subscription $subscription
     * @param array $categories
     * @return void
     * @validate $subscription \DPN\Dmailsubscribe\Validation\Validator\SubscriptionValidator
     */
    public function subscribeAction(Subscription $subscription, array $categories = [])
    {
        $categoryPids = $this->settingsService->getSetting('categoryPids', [], ',');

        $selectedCategories = $this->categoryRepository->findAllByUids($categories, $categoryPids);
        if (count($selectedCategories) > 0) {
            foreach ($selectedCategories as $category) {
                $subscription->addCategory($category);
            }
        }

        $this->subscriptionRepository->add($subscription);

        /** @var PersistenceManagerInterface $persistenceManager */
        $persistenceManager = $this->objectManager->get(PersistenceManagerInterface::class);
        $persistenceManager->persistAll();

        $templateVariables = array(
            'subscription' => $subscription,
            'confirmationCode' => $this->generateConfirmationCode($subscription->getUid()),
        );

        /** @var EmailService $emailService */
        $emailService = $this->objectManager->get(EmailService::class);
        $emailService->send(
            $subscription->getEmail(),
            $subscription->getName(),
            'NewSubscription',
            $subscription->getReceiveHtml(),
            $templateVariables
        );

        $message = LocalizationUtility::translate('message.subscribe.success', $this->extensionName);
        $this->addFlashMessage($message);

        $this->redirect('message');
    }

    /**
     * @param integer $uid
     * @return string
     */
    private function generateConfirmationCode($uid)
    {
        return GeneralUtility::stdAuthCode($uid);
    }

    /**
     * @param string $subscriptionUid
     * @param string $confirmationCode
     * @return void
     */
    public function confirmAction($subscriptionUid, $confirmationCode)
    {
        $muteConfirmationErrors = (boolean)$this->settingsService->getSetting('muteConfirmationErrors', true);

        if (false === ($confirmationCodeValid = $this->validateConfirmationCode($subscriptionUid, $confirmationCode))) {
            if (false === $muteConfirmationErrors) {
                $message = LocalizationUtility::translate('message.confirm.confirmation_code_invalid', $this->extensionName);
                $this->addFlashMessage($message);
                $this->redirect('message');
            }
        }

        /** @var Subscription $subscription */
        if (null === ($subscription = $this->subscriptionRepository->findNotConfirmedByUid($subscriptionUid))) {
            if (false === $muteConfirmationErrors) {
                $message = LocalizationUtility::translate('message.confirm.subscription_not_found', $this->extensionName);
                $this->addFlashMessage($message);
                $this->redirect('message');
            }
        }

        if (true === $confirmationCodeValid && null !== $subscription && true === $subscription->getHidden()) {
            $subscription->setHidden(false);
            $this->subscriptionRepository->update($subscription);
            $message = LocalizationUtility::translate('message.confirm.success', $this->extensionName);
            $this->addFlashMessage($message);
            $this->redirect('message');
        }

        $this->redirect('new');
    }

    /**
     * @param integer $uid
     * @param string $confirmationCode
     * @return boolean
     */
    private function validateConfirmationCode($uid, $confirmationCode)
    {
        $confirmationCodeForUid = $this->generateConfirmationCode($uid);
        return $confirmationCodeForUid === $confirmationCode;
    }

    /**
     * @param integer $subscriptionUid
     * @param string $confirmationCode
     * @return void
     */
    public function unsubscribeAction($subscriptionUid, $confirmationCode)
    {
        $muteUnsubscribeErrors = (boolean)$this->settingsService->getSetting('muteUnsubscribeErrors', true);

        if (false === ($confirmationCodeValid = $this->validateConfirmationCode($subscriptionUid, $confirmationCode))) {
            if (false === $muteUnsubscribeErrors) {
                $message = LocalizationUtility::translate('message.unsubscribe.confirmation_code_invalid', $this->extensionName);
                $this->addFlashMessage($message);
                $this->redirect('message');
            }
        }

        /** @var Subscription $subscription */
        if (null === ($subscription = $this->subscriptionRepository->findByUid($subscriptionUid))) {
            if (false === $muteUnsubscribeErrors) {
                $message = LocalizationUtility::translate('message.unsubscribe.subscription_not_found', $this->extensionName);
                $this->addFlashMessage($message);
                $this->redirect('message');
            }
        }

        if (true === $confirmationCodeValid && null !== $subscription) {
            $this->subscriptionRepository->remove($subscription);
            $message = LocalizationUtility::translate('message.unsubscribe.success', $this->extensionName);
            $this->addFlashMessage($message);
            $this->redirect('message');
        }

        $this->redirect('new');
    }

    /**
     * @param string $email
     * @return void
     */
    public function unsubscribeformAction($email = null)
    {
        if (null !== $email) {
            $muteUnsubscribeErrors = (boolean)$this->settingsService->getSetting('muteUnsubscribeErrors', true);
            $lookupPageIds = $this->settingsService->getSetting('lookupPids', array(), ',');
            if (null === ($subscription = $this->subscriptionRepository->findByEmail($email, $lookupPageIds))) {
                if (false === $muteUnsubscribeErrors) {
                    $message = LocalizationUtility::translate('message.unsubscribe.subscription_not_found', $this->extensionName);
                    $this->addFlashMessage($message);
                }
            } else {
                $this->subscriptionRepository->remove($subscription);
                $message = LocalizationUtility::translate('message.unsubscribe.success', $this->extensionName);
                $this->addFlashMessage($message);
                $this->redirect('message');
            }
        }
        $pluginPageUid = $this->settingsService->getSetting('pluginPageUid');
        $unsubscribePageUid = $this->settingsService->getSetting('unsubscribePageUid', $pluginPageUid);

        $this->view->assign('pluginPageUid', $pluginPageUid);
        $this->view->assign('unsubscribePageUid', $unsubscribePageUid);
    }

    /**
     * @return void
     */
    public function messageAction()
    {
    }

    /**
     * Override parent method to disable output of
     * controller errors as flash messages
     *
     * @return boolean
     */
    public function getErrorFlashMessage()
    {
        return false;
    }
}
