<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

call_user_func(function ($_EXTKEY) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
		$_EXTKEY,
		'auth',
		\WebentwicklerAt\Loginlimit\Service\AuthenticationService::class,
		[
			'title' => 'User authentication',
			'description' => 'Authentication with username/password.',
			'subtype' => 'getUserBE,authUserBE,getUserFE,authUserFE',
			'available' => true,
			// must be higher than \TYPO3\CMS\Sv\AuthenticationService (50), rsaauth (60) and OpenID (75)
			'priority' => 90,
			'quality' => 50,
			'os' => '',
			'exec' => '',
			'className' => \WebentwicklerAt\Loginlimit\Service\AuthenticationService::class,
		]
	);

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY]
        = \WebentwicklerAt\Loginlimit\Hook\UserAuthentication::class . '->postUserLookUp';

	if (TYPO3_MODE === 'BE') {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \WebentwicklerAt\Loginlimit\Command\TaskCommandController::class;
	}
}, $_EXTKEY);
