<?php
return array(
	'ctrl' => array(
		'title' => 'LLL:EXT:loginlimit/Resources/Private/Language/locallang.xlf:tx_loginlimit_domain_model_loginattempt',
		'label' => 'tstamp',
		'label_userFunc' => 'WebentwicklerAt\\Loginlimit\\Userfuncs\\Tca->loginAttemptTitle',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('loginlimit') . 'Resources/Public/Icons/tx_loginlimit_domain_model_loginattempt.png',
		'default_sortby' => 'tstamp DESC',
		'tstamp' => 'tstamp',
		'rootLevel' => 1,
		'dividers2tabs' => TRUE,
		'adminOnly' => TRUE,
		'searchFields' => 'ip,username'
	),
	'interface' => array(
		'showRecordFieldList' => 'tstamp,username,ip'
	),
	'columns' => array(
		'tstamp' => array(
			'label' => 'LLL:EXT:loginlimit/Resources/Private/Language/locallang.xlf:tstamp',
			'config' => array(
				'type' => 'input',
				'size' => '13',
				'eval' => 'datetime',
				'readOnly' => TRUE
			)
		),
		'ip' => array(
			'label' => 'LLL:EXT:loginlimit/Resources/Private/Language/locallang.xlf:ip',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'readOnly' => TRUE
			)
		),
		'username' => array(
			'label' => 'LLL:EXT:loginlimit/Resources/Private/Language/locallang.xlf:username',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'readOnly' => TRUE
			)
		)
	),
	'types' => array(
		'0' => array(
			'showitem' => 'tstamp,ip,username'
		)
	)
);