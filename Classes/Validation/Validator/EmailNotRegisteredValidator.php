<?php

namespace DPN\Dmailsubscribe\Validation\Validator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Björn Fromme <fromme@dreipunktnull.come>
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

use DPN\Dmailsubscribe\Domain\Repository\SubscriptionRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator: Email must not be registered
 *
 * Returns FALSE as validation result if email is already registered.
 *
 * @package Dmailsubscribe
 * @subpackage Validation/Validator
 */
class EmailNotRegisteredValidator extends AbstractValidator
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;


    protected $supportedOptions = [
        'lookupPageIds' => [
            // Default value
            0,
            // Default message
            'Page ID for subscribed email lookup',
            // Type of the option
            'integer'
        ]
    ];

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        /** @var SubscriptionRepository $repository */
        $repository = $this->objectManager->get(SubscriptionRepository::class);

        $result = $repository->findByEmail($value, $this->options['lookupPageIds']);

        if (null !== $result) {
            $this->addError('The given email address is already registered.', 1367223995);
            return false;
        }

        return true;
    }
}
