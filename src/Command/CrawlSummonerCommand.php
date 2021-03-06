<?php

namespace App\Command;

use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Utils\Locker\Locker;
use App\Utils\Locker\LockerException;
use App\Utils\LS\Crawl;
use App\Utils\LS\LSException;
use App\Utils\RiotApi\Region;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlSummonerCommand extends Command
{
    protected static $defaultName = 'crawl:summoner';
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

    protected function configure()
    {
        $this
            ->setDescription('Crawl all Summoners of Streamers that are online')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Enable Debug')
            ->addArgument('force', InputArgument::OPTIONAL, 'Force Execution even if .lock exists');;
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
        $debug = $input->getArgument('debug') === 'y';
        $force = $input->getArgument('force') === 'y';

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
         * All Streamers which are online
         */
        $streamers = $this->em->getRepository(Streamer::class)->findBy(array(
            'isOnline' => true,
        ));

        $lsCrawl = new Crawl($this->em);

        foreach ($streamers as $streamer) {

            $debug ? $io->note('Checking Streamer ' . $streamer->getChannelName()) : null;

            /* @var $summoner Summoner */
            foreach ($lsCrawl->summoners($streamer) as $summoner) {

                try {
                    $isPlaying = $lsCrawl->check_game_summoner($summoner, true);
                } catch (LSException $e) {
                    $isPlaying = false;
                }
                $text = $isPlaying ? '<fg=green>InGame</>, skipping rest...' : '<fg=red>Not InGame</>';

                if ($debug) {
                    if ($isPlaying) {
                        $io->text('-->Summoner ' . Region::name($summoner->getRegion()) . '-' . $summoner->getName() . ': ' . $text);
                    } else {
                        $io->text('-->Summoner ' . Region::name($summoner->getRegion()) . '-' . $summoner->getName() . ': ' . $text);
                    }
                }

                /**
                 * Do not crawl more Summoners if one is playing...
                 */
                if ($isPlaying) {
                    break;
                }

            }
        }

        /**
         * Remove the Lockfile
         */
        Locker::remove(__FILE__);

        try {
            $debug ? $io->comment('Finished. Duration: ' . StopWatch::stop($start)) : null;
        } catch (StopwatchException $e) {
            throw new StopwatchException('Exception with Stopping Timer. ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
