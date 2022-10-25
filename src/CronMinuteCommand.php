<?php
namespace App;

use App\Service\ActionHelper;
use App\Service\ReadingHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronMinuteCommand extends Command
{
    protected static $defaultName = 'app:cron';

    /**
     * @var ActionHelper
     */
    private $actionHelper;

    /**
     * @var ReadingHelper
     */
    private $readingHelper;

    public function __construct(ReadingHelper $readingHelper, ActionHelper $actionHelper)
    {
        parent::__construct();

        $this->readingHelper = $readingHelper;
        $this->actionHelper = $actionHelper;
    }

    protected function configure()
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if($this->readingHelper->connectionIsDown() && $this->readingHelper->getLastConnectionValue()) {
            $this->readingHelper->connectionChanged(false);
        }

        $this->actionHelper->handleAllAutomations();

        return Command::SUCCESS;
    }
}