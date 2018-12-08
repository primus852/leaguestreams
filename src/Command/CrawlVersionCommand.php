<?php

namespace App\Command;

use App\Entity\Champion;
use App\Entity\Versions;
use App\Utils\Locker\Locker;
use App\Utils\Locker\LockerException;
use App\Utils\LS\Crawl;
use App\Utils\LS\LSException;
use App\Utils\RiotApi\RiotApi;
use App\Utils\RiotApi\RiotApiException;
use App\Utils\RiotApi\Settings;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CrawlVersionCommand extends Command
{
    protected static $defaultName = 'crawl:version';
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
            ->setDescription('Crawl endpoints of the Riot Api')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Enable Debug')
            ->addArgument('force', InputArgument::OPTIONAL, 'Force Execution even if .lock exists');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws LSException
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
         * New Crawler
         */
        $lsCrawl = new Crawl($this->em);

        try{
            $lsCrawl->versions();
            $debug ? $io->text('Updated Versions: <fg=green>success</>') : null;
        }catch (LSException $e){
            throw new LSException('Could not gather Versions: '.$e->getMessage());
        }

        /**
         * Gather all champions from ddragon
         */
        $champions = $lsCrawl->update_champions();
        $debug ? $io->text('Gathered all Champions: <fg=green>success</>') : null;

        /**
         * Loop through champions
         * @var $champion Champion
         */
        foreach($champions['data'] as $champion_data){

            $id = (int)$champion_data['key'];

            /**
             * Check if Champion exists
             */
            $champion = $this->em->getRepository(Champion::class)->find($id);

            $status = '<fg=yellow>updated</>';
            if($champion === null){
                $champion = new Champion();
                $champion->setId($id);
                $status = '<fg=green>created</>';
            }

            $name = $champion_data['id'];

            /**
             * Use NA1 for Static
             */
            $api = new RiotApi(new Settings());

            /**
             * Get the current Version
             */
            $version = $this->em->getRepository(Versions::class)->find(1);

            try {
                $champion_api = $api->getChampion($version->getChampion(), $name);
            } catch (RiotApiException $e) {
                throw new LSException('Gather Versions Exception: ' . $e->getMessage());
            }

            $champion->setModified(new \DateTime());
            $champion->setName($name);
            $champion->setTitle($champion_api['data'][$name]['title']);
            $champion->setImage($champion_api['data'][$name]['image']['full']);
            $champion->setChampKey($champion_api['data'][$name]['id']);
            $champion->setBlurb($champion_api['data'][$name]['blurb']);
            $champion->setLore($champion_api['data'][$name]['lore']);
            $champion->setSpellPassiveImage($champion_api['data'][$name]['passive']['image']['full']);
            $champion->setSpellPassiveName($champion_api['data'][$name]['passive']['name']);
            $champion->setSpellPassiveDescription($champion_api['data'][$name]['passive']['description']);
            $champion->setSpellQImage($champion_api['data'][$name]['spells'][0]['image']['full']);
            $champion->setSpellQName($champion_api['data'][$name]['spells'][0]['name']);
            $champion->setSpellQDescription($champion_api['data'][$name]['spells'][0]['description']);
            $champion->setSpellEImage($champion_api['data'][$name]['spells'][1]['image']['full']);
            $champion->setSpellEName($champion_api['data'][$name]['spells'][1]['name']);
            $champion->setSpellEDescription($champion_api['data'][$name]['spells'][1]['description']);
            $champion->setSpellWImage($champion_api['data'][$name]['spells'][2]['image']['full']);
            $champion->setSpellWName($champion_api['data'][$name]['spells'][2]['name']);
            $champion->setSpellWDescription($champion_api['data'][$name]['spells'][2]['description']);
            $champion->setSpellRImage($champion_api['data'][$name]['spells'][3]['image']['full']);
            $champion->setSpellRName($champion_api['data'][$name]['spells'][3]['name']);
            $champion->setSpellRDescription($champion_api['data'][$name]['spells'][3]['description']);
            $this->em->persist($champion);

            $debug ? $io->text('-->Champion: <fg=green>'.$champion->getName().'</>: '.$status) : null;

            /**
             * Reset the Autogenerated ID
             */
            $metadata = $this->em->getClassMetaData(get_class($champion));
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());

        }

        $debug ? $io->text('Flushing DB:') : null;

        try{
            $this->em->flush();
            $debug ? $io->text('--><fg=green>success</>') : null;
        }catch (\Exception $e){
            throw new LSException('MySQL Error: '.$e->getMessage());
        }



        /**
         * Remove the Lockfile
         */
        Locker::remove(__FILE__);

        try {
            $debug ? $io->comment('Finished. Duration: ' . StopWatch::stop($start) ) : null;
        } catch (StopwatchException $e) {
            throw new StopwatchException('Exception with Stopping Timer. ' . $e->getMessage());
        }
    }
}
