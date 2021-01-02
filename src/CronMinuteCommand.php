<?php
namespace App;

use App\Service\ReadingHelper;

class CronMinuteCommand extends Command
{
    protected static $defaultName = 'app:cron';

    protected function configure()
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output, ReadingHelper $readingHelper)
    {
        if($readingHelper->connectionIsDown() && $readingHelper->getLastConnectionValue()) {
            $readingHelper->connectionChanged(false);
        }

        return Command::SUCCESS;
    }
}