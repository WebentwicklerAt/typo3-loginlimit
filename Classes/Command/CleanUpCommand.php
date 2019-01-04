<?php
declare(strict_types=1);
namespace WebentwicklerAt\Loginlimit\Command;

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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use WebentwicklerAt\Loginlimit\Service\CleanUpService;

/**
 * Extbase CommandController Task
 *
 * @author Dmytro Nozdrin <public.team.osi@gmail.com>
 */
class CleanUpCommand extends Command
{
    /**
     * Configures the command by setting its name, description and options.
     *
     * @return void
     */
    public function configure()
    {
        $this
            ->setDescription('Clean up expired entries.')
            ->setHelp('Deletes expired login attempts and bans.');
    }

    /**
     * Executes the command to delete expired login attempts and bans.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var CleanUpService $service */
        $service = GeneralUtility::makeInstance(ObjectManager::class)->get(CleanUpService::class);
        $service->deleteExpiredEntries();

        $io = new SymfonyStyle($input, $output);
        $io->success('Expired login attempts and bans were successfully deleted');
    }
}
