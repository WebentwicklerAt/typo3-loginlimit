.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _user-manual:

Users Manual
============

Import and install the newest version of this extension from TYPO3 Extension Repository (TER).

Configuration
-------------

The configuration is done in the extension configuration of "Extension Manager".

* enableBackend: Enable login limit for Backend.
* enableFrontend: Enable login limit for Frontend.
* enableCleanUpAtLogin: Enable clean up expired entries at login, alternatively a scheduler task can be set-up.
* delayLogin: Every failed login attempt delays login for 1 second. Max. 10 seconds.
* findtime: Time frame (in seconds) to look for failed login attempts.
* maxretry: Number of failed login attempts within findtime causing a ban for bantime.
* bantime: Duration (in seconds) to be banned for. Negative number for "permanent" ban.

Add scheduler task
------------------

First of all please make sure that you have installed and set-up the extension "scheduler" properly. Therefore the "Setup check" is provided in the module "Scheduler".

In module "Scheduler" add a new task, select "Execute console commands" as "Class", set desired "Frequency" and select "loginlimit:clear: Clean up expired entries." as "Schedulable Command".

If clean-up is done through scheduler task, the option "enableCleanUpAtLogin" in extension configuration should be disabled.
