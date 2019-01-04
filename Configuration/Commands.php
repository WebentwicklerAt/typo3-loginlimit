<?php
defined('TYPO3_MODE') or die();

return [
    'loginlimit:clear' => [
        'class' => \WebentwicklerAt\Loginlimit\Command\CleanUpCommand::class,
    ],
];
