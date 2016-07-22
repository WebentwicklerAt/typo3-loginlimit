<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'Login limit',
	'description' => 'Protect backend and/or frontend login from brute-force attacks.',
	'category' => 'misc',
	'version' => '1.0.1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 1,
	'author' => 'Gernot Leitgab',
	'author_email' => 'typo3@webentwickler.at',
	'author_company' => 'Webentwickler.at',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.6.99',
			'scheduler' => '6.2.0-7.6.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:12:{s:9:"ChangeLog";s:4:"439f";s:25:"class.ux_tx_scheduler.php";s:4:"d63e";s:16:"ext_autoload.php";s:4:"b1c2";s:21:"ext_conf_template.txt";s:4:"7abb";s:12:"ext_icon.gif";s:4:"97d0";s:17:"ext_localconf.php";s:4:"94ec";s:13:"locallang.xml";s:4:"b492";s:17:"doc/manual.de.sxw";s:4:"838a";s:14:"doc/manual.sxw";s:4:"1f04";s:34:"eid/class.tx_schedulerhttp_eid.php";s:4:"cdbd";s:39:"tasks/class.tx_schedulerhttp_geturl.php";s:4:"a114";s:63:"tasks/class.tx_schedulerhttp_geturl_additionalfieldprovider.php";s:4:"752d";}',
);
