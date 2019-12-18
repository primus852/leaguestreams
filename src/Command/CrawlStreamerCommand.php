<?php

namespace App\Command;

use App\Entity\Streamer;
use App\Utils\Locker\Locker;
use App\Utils\Locker\LockerException;
use App\Utils\StreamPlatform\StreamPlatformException;
use App\Utils\StreamPlatform\TwitchApi;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlStreamerCommand extends Command
{
    protected static $defaultName = 'crawl:streamer';
    private $em;

    /**
     * CrawlStreamerCommand constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Crawl all Streamers and check if they are online and playing League')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Enable Debug')
            ->addArgument('force', InputArgument::OPTIONAL, 'Force Execution even if .lock exists');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws LockerException
     * @throws StopwatchException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $debug = $input->getArgument('debug') === 'y' ? true : false;
        $force = $input->getArgument('force') === 'y' ? true : false;

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

        /**
         * All Streamers
         */
        $streamers = $this->em->getRepository(Streamer::class)->findAll();
        $streamCount = 0;
        $count_online = 0;
        $count_offline = 0;

        foreach ($streamers as $streamer) {

            if ($streamer->getPlatform()->getName() === 'Twitch.tv') {
                $streamCount++;

                $api = new TwitchApi($this->em);
                $isOnline = false;
                try {
                    $isOnline = $api->check_online($streamer->getChannelId(), true);
                } catch (StreamPlatformException $e) {
                    $io->error('Exception: ' . $e->getMessage());
                }

                if ($isOnline) {
                    $count_online++;
                    $debug ? $io->success('Streamer ' . $streamer->getChannelUser() . ' is Online') : null;
                } else {
                    $count_offline++;
                    $debug ? $io->warning('Streamer ' . $streamer->getChannelUser() . ' is Offline') : null;
                }


            } else {
                $debug ? $io->error('Platform not implemented: ' . $streamer->getPlatform()->getName()) : null;
            }
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
    }
}
