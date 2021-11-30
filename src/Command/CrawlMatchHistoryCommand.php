<?php

namespace App\Command;

use App\Entity\Match;
use App\Utils\Locker\Locker;
use App\Utils\Locker\LockerException;
use App\Utils\LS\Crawl;
use App\Utils\LS\LSException;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlMatchHistoryCommand extends Command
{
    protected static $defaultName = 'crawl:match_history';
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
            ->setDescription('Check for uncrawled games and update the Match')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Enable Debug')
            ->addArgument('force', InputArgument::OPTIONAL, 'Force Execution even if .lock exists');
        ;
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

        $lsCrawl = new Crawl($this->em);

        $uncrawled = $this->em->getRepository(Match::class)->findBy(array(
            'crawled' => false,
        ), array(
            'matchId' => 'DESC'
        ), 500);

        $debug ? $io->note('Crawling uncrawled Games') : null;
        foreach($uncrawled as $uc){

            try{
                $result = $lsCrawl->update_match($uc);
                $colorText = $result ? '<fg=green>updated</>' : '<fg=red>not found</>';
                $debug ? $io->text('-->Game '.$uc->getMatchId().' / '.$uc->getSummoner()->getRegion()->getShort().'-'.$uc->getSummoner()->getName().' '.$colorText) : null;

                /**
                 * Update current LeagueStats of Summoner
                 */
                $lsCrawl->update_summoner($uc->getSummoner());
                $debug ? $io->text('-->Update Stats: <fg=green>success</>') : null;

            }catch (LSException $e){
                $io->error('Exception: '.$e->getMessage());
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
