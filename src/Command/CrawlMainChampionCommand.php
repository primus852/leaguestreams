<?php

namespace App\Command;

use App\Entity\Champion;
use App\Utils\LSFunction;
use Doctrine\Common\Persistence\ObjectManager;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlMainChampionCommand extends Command
{
    protected static $defaultName = 'crawl:main:champion';
    private $em;

    /**
     * CrawlMainChampionCommand constructor.
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
            ->setDescription('Crawl Main Champions for all Champions')
            ->addArgument('champion', InputArgument::OPTIONAL, 'Champion Name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $champion = $input->getArgument('champion');

        if($champion !== null && $champion !== ''){
            $champs = $this->em->getRepository(Champion::class)->findBy(array(
                'champKey' => $champion,
            ));
        }else{
            $champs = $this->em->getRepository(Champion::class)->findAll();
        }

        /**
         * Init LSFunction
         */
        $ls = new LSFunction($this->em);

        foreach($champs as $champ){

            /**
             * Start Stopwatch
             */
            $start = Stopwatch::start();

            $io->text('Checking: <fg=green>'.$champ->getName().'</>');

            $result = $ls->getMainStreamer($champ);

            /**
             * Output
             */
            $table = new Table($output);
            $table->setHeaders(array('Champion','Streamer','% played', 'Total'));

            foreach($result as $r){
                $table->addRow(array(
                    $champ->getName(),
                    $r['details']['name'],
                    $r['pct'],
                    $r['games'].' / '.$r['all']
                ));
            }

            $table->render();

            try {
                $io->text('Finished. Duration: <fg=green>' . StopWatch::stop($start).'</>');
            } catch (StopwatchException $e) {
                throw new StopwatchException('Exception with Stopping Timer. ' . $e->getMessage());
            }

        }

        $io->success('Main Streamer crawled');
    }
}
