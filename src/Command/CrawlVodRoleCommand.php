<?php

namespace App\Command;

use App\Utils\Locker\Locker;
use App\Utils\Locker\LockerException;
use App\Utils\LS\VodHandler;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

class CrawlVodRoleCommand extends Command
{
    protected static $defaultName = 'crawl:vod:role';
    private $em;
    private $router;

    /**
     * CrawlVodRoleCommand constructor.
     * @param ObjectManager $em
     * @param RouterInterface $router
     */
    public function __construct(ObjectManager $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Crawl VODs per Role')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Enable Debug')
            ->addArgument('force', InputArgument::OPTIONAL, 'Force Execution even if .lock exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $debug = $input->getArgument('debug') === 'y';
        $force = $input->getArgument('force') === 'y';

        /**
         * All Roles
         */
        $roles = array(
            'Top'
        );

        /**
         * Start Stopwatch
         */
        $start = Stopwatch::start();

        /**
         * Check if it already running
         */
        try {
            if (Locker::check_lock(__FILE__, $force)) {
                $io->error('Lockfile already exists: ' . __FILE__ . Locker::EXT);
                exit();
            }
        } catch (LockerException $e) {
            throw new LockerException($e->getMessage());
        }

        /**
         * Create the Lockfile
         */
        Locker::touch(__FILE__);

        $vods = new VodHandler($this->em, $this->router);

        foreach($roles as $role){
            $vodsRole = $vods->by_role($role);

            dump($vodsRole);
            die;

        }

        /**
         * Remove the Lockfile
         */
        Locker::remove(__FILE__);

        try {
            $perSecond = round(StopWatch::stop($start, true) / $streamCount, 2);
            $debug ? $io->comment('Finished. Duration: ' . StopWatch::stop($start) .
                ' (' . $perSecond . ' s/Streamer).' .
                ' Online: ' . $count_online . '|Offline: ' . $count_offline) : null;
        } catch (StopwatchException $e) {
            throw new StopwatchException('Exception with Stopping Timer. ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
