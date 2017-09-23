<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'Login limit',
	'description' => 'Protect backend and/or frontend login against brute-force attacks.',
	'category' => 'misc',
	'version' => '1.0.3',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 1,
	'author' => 'Gernot Leitgab',
	'author_company' => 'Webentwickler.at',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-8.7.99',
			'scheduler' => '6.2.0-8.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);
