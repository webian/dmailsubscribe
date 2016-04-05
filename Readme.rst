TYPO3 Extension Dmailsubscribe
==============================

This extension provides a frontend plugin containing a registration form for **direct_mail** newsletters and
handles confirmation and unsubscription requests.

Features
--------

* based on extbase/fluid
* supports additional fields gender, name, first_name, last_name and company
* supports categories
* includes viewhelpers for confirm and unsubscribe links
* additional and required fields configurable via TS or flexform

Example configuration
---------------------

::

    plugin.tx_dmailsubscribe {
      view {
        # Overrides the template paths
        layoutRootPaths.100 = EXT:myext/Resources/Private/Layouts/Plugins/Dmailsubscribe/
        templateRootPaths.100 = EXT:myext/Resources/Private/Templates/Plugins/Dmailsubscribe/
        partialRootPaths.100 = EXT:myext/Resources/Private/Partials/Plugins/Dmailsubscribe/
      }
      settings {
        additionalFields = gender, name, company, receiveHtml, categories
        requiredFields = gender, name
        muteConfirmationErrors = 1
        muteUnsubscribeErrors = 1
        lookupPids = 1,2,3
        categoryPids = 1,2,3
        pluginPageUid = 1
        fromEmail = me@domain.tld
        fromName = Newsletter
        subject = Your subscribtion to our newsletter
      }
    }

* additionalFields: additional fields to render in the form apart from 'email' which is always rendered
* requiredFields: fields to make, well, required
* muteConfirmationErrors: if set to TRUE errors for invalid confirmation requests are hidden
* muteUnsubscribeErrors: if set to TRUE errors for invalid unsubscribe requests are hidden
* lookupPids: PIDs to look up existing subscriptions to avoid duplicates
* categoryPids: PIDs containing direct_mail categories
* pluginPageUid: UID of the page including the plugin
* fromEmail: Email of sender used in confirmation emails
* fromName: Name of sender used in confirmation emails
* subject: Subject of subscription confirmation email

Customizing templates
---

You can use your own fluid templates with this extension via the standard template overloading mechanism (see above).

E-Mail Templates are resolved to templateRootPaths.{$index}/Email/NewSubscription.html and templateRootPaths.{$index}/Email/NewSubscription.txt.

ToDo
----

- currently only tt_address is supported
- flexible CAPTCHA support

CodeStyle - Fixer
-----------------

::

    $ composer global require fabpot/php-cs-fixer
    $ php-cs-fixer fix --config-file Build/.php_cs
