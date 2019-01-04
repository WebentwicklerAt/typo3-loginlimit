<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Login limit',
    'description' => 'Protect backend and/or frontend login against brute-force attacks.',
    'category' => 'misc',
    'version' => '1.1.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Gernot Leitgab',
    'author_company' => 'Webentwickler.at',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
            'scheduler' => '8.7.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
