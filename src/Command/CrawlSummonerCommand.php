<?php

namespace App\Command;

use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Utils\LS\Crawl;
use App\Utils\LS\CrawlException;
use App\Utils\RiotApi\Region;
use Doctrine\Common\Persistence\ObjectManager;
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
            ->addArgument('debug', InputArgument::OPTIONAL, 'Enable Debug');
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws StopwatchException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $debug = $input->getArgument('debug') === 'y' ? true : false;

        /**
         * Start Stopwatch
         */
        $start = Stopwatch::start();

        /**
         * All Streamers which are online
         */
        $streamers = $this->em->getRepository(Streamer::class)->findBy(array(
            'isOnline' => true,
        ));

        $lsCrawl = new Crawl($this->em);

        foreach($streamers as $streamer){

            $debug ? $io->note('Checking Streamer '.$streamer->getChannelName()) : null;

            /* @var $summoner Summoner */
            foreach($lsCrawl->summoners($streamer) as $summoner){

                try{
                    $isPlaying = $lsCrawl->check_game_summoner($summoner, true);
                }catch (CrawlException $e){
                    $isPlaying = false;
                }
                $text = $isPlaying ? '<fg=green>InGame</>, skipping rest...' : '<fg=red>Not InGame</>';

                if($debug){
                    if($isPlaying){
                        $io->text('-->Summoner '.Region::name($summoner->getRegion()).'-'.$summoner->getName().': '.$text);
                    }else{
                        $io->text('-->Summoner '.Region::name($summoner->getRegion()).'-'.$summoner->getName().': '.$text);
                    }
                }

                /**
                 * Do not crawl more Summoners if one is playing...
                 */
                if($isPlaying){
                    break;
                }

            }
        }

        try {
            $debug ? $io->comment('Finished. Duration: ' . StopWatch::stop($start)) : null;
        } catch (StopwatchException $e) {
            throw new StopwatchException('Exception with Stopping Timer. ' . $e->getMessage());
        }
    }
}
