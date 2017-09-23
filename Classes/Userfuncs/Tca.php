<?php
namespace WebentwicklerAt\Loginlimit\Userfuncs;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * User functions for rendering title of records
 *
 * @author Gernot Leitgab <https://webentwickler.at>
 */
class Tca {
	/**
	 * Title for login attempt table
	 *
	 * @param array $parameters
	 * @param mixed $parentObject
	 * @return void
	 */
	public function loginAttemptTitle(&$parameters, $parentObject) {
		$this->getTitle($parameters);
	}

	/**
	 * Title for ban table
	 *
	 * @param array $parameters
	 * @param mixed $parentObject
	 * @return void
	 */
	public function banTitle(&$parameters, $parentObject) {
		$this->getTitle($parameters);
	}

	/**
	 * Generic title
	 *
	 * @param array $parameters
	 * @return void
	 */
	protected function getTitle(&$parameters) {
		$record = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);

		$title = array();
		$title[] = date(
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
			$record['tstamp']
		);
		if (strlen($record['username'])) {
			$title[] = $record['username'];
		}
		if (strlen($record['ip'])) {
			$title[] = $record['ip'];
		}
		$parameters['title'] = implode(' / ', $title);
	}
}