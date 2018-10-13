<?php

namespace App\Command;

use App\Entity\Match;
use App\Utils\LS\Crawl;
use App\Utils\LS\CrawlException;
use Doctrine\Common\Persistence\ObjectManager;
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

    protected function configure()
    {
        $this
            ->setDescription('Check for uncrawled games and update the Match')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Enable Debug');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $debug = $input->getArgument('debug') === 'y' ? true : false;

        $lsCrawl = new Crawl($this->em);

        $uncrawled = $this->em->getRepository(Match::class)->findBy(array(
            'crawled' => false,
        ));

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

            }catch (CrawlException $e){
                $io->error('Exception: '.$e->getMessage());
            }
        }
    }
}
