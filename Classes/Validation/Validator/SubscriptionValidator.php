<?php

namespace DPN\Dmailsubscribe\Validation\Validator;

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

use DPN\Dmailsubscribe\Domain\Model\Subscription;
use DPN\Dmailsubscribe\Service\SettingsService;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidSubjectException;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;

/**
 * Validator: Subscription object validation wrapper
 *
 * Validates an entire Subscription instance using various other
 * Validators as configured in TypoScript.
 *
 * @package Dmailsubscribe
 * @subpackage Validation/Validator
 */
class SubscriptionValidator extends GenericObjectValidator
{
    /**
     * @var \DPN\Dmailsubscribe\Service\SettingsService
     * @inject
     */
    protected $settingsService;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @param \DPN\Dmailsubscribe\Service\SettingsService $settingsService
     * @return void
     */
    public function injectSettingsService(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Overrides parent::validate() to add notEmptyValidators
     * for all required fields and emailNotRegisteredValidator
     * to email field
     *
     * @param mixed $object
     * @throws InvalidSubjectException
     * @return Result
     */
    public function validate($object)
    {
        if (false === $this->canValidate($object)) {
            throw new InvalidSubjectException(sprintf('Expected "%s" but was "%s"', 'Tx_Dmailsubscribe_Domain_Model_Subscription', get_class($object)));
        }

        $requiredFields = $this->settingsService->getSetting('requiredFields', array(), ',');
        $lookupPageIds = $this->settingsService->getSetting('lookupPids', array(), ',');

        /** @var EmailNotRegisteredValidator $emailNotRegisteredValidator */
        $emailNotRegisteredValidator = $this->objectManager->get(EmailNotRegisteredValidator::class, ['lookupPageIds' => $lookupPageIds]);
        $this->addPropertyValidator('email', $emailNotRegisteredValidator);

        foreach ($requiredFields as $field) {
            /** @var NotEmptyValidator $notEmptyValidator */
            $notEmptyValidator = $this->objectManager->get(NotEmptyValidator::class);
            $this->addPropertyValidator($field, $notEmptyValidator);
        }

        return parent::validate($object);
    }

    /**
     * @param object $object
     * @return boolean
     */
    public function canValidate($object)
    {
        return $object instanceof Subscription;
    }
}
