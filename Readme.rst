TYPO3 Extension Dmailsubscribe
==============================

This extension provides a frontend plugin containing a registration form for **direct_mail** newsletters and
handles confirmation and unsubscription requests.

Features
--------

* based on extbase/fluid
* supports additional fields gender, name, and company
* supports categories
* includes viewhelpers for confirm and unsubscribe links
* additional and required fields configurable via TS or flexform

Example configuration
---------------------

::

    plugin.tx_dmailsubscribe {
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

ToDo
----

- currently only tt_address is supported
- flexible CAPTCHA support
