<?php

namespace App\Command;

use App\Entity\Summoner;
use App\Utils\Locker\Locker;
use App\Utils\Locker\LockerException;
use App\Utils\LS\LSException;
use App\Utils\RiotApi\RiotApi;
use App\Utils\RiotApi\RiotApiException;
use App\Utils\RiotApi\Settings;
use Doctrine\Common\Persistence\ObjectManager;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class CrawlSummonerNameCommand extends Command
{
    protected static $defaultName = 'crawl:summoner_name';
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
            ->setDescription('Update the Summoner Names')
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

        $debug ? $io->note('Gathering all summoners') : null;

        /**
         * Get all Summoners
         */
        $summoners = $this->em->getRepository(Summoner::class)->findAll();

        /**
         * Loop through all to update names
         * @var $summoner Summoner
         */
        foreach ($summoners as $summoner) {

            $api = new RiotApi(new Settings(), null, $summoner->getRegion()->getLong());

            $debug ? $io->text('Summoner: <fg=green>' . $summoner->getRegion()->getShort() . '-' . $summoner->getName() . '</>') : null;

            try {

                /**
                 * If we already have the upgraded ID/AccID, use them
                 */
                $upgrade = strlen($summoner->getSummonerId()) > 12 ? true : false;
                $isV4 = $upgrade ? '<fg=green>yes</>' : '<fg=red>no</>';
                $debug ? $io->text('-->V4: ' . $isV4) : null;

                $info = $api->getSummoner($summoner->getSummonerId(), false, $upgrade);

            } catch (RiotApiException $e) {
                throw new LSException('Summoner Info Exception: ' . $e->getMessage());
            }

            $debug ? $io->text('-->Found Summoner Name is: <fg=green>' . $info['name'] . '</> updating Summoner ID and Account ID...') : null;

            /**
             * With the freshly updated name, crawl the new V4 API to get the encrypted Account ID
             */
            if ($isV4 === '<fg=red>no</>') {
                try {
                    $info = $api->getSummonerByName($info['name'], true);
                } catch (RiotApiException $e) {
                    throw new LSException('Summoner Info Upgrade Exception: ' . $e->getMessage());
                }
            }

            $updated = $info['name'] === $summoner->getName() ? '<fg=yellow>unchanged</>' : '<fg=green>updated to ' . $info['name'] . '</>';

            $debug ? $io->text('-->Name: ' . $updated) : null;
            $debug ? $io->text('-->AccountId: ' . $info['accountId']) : null;
            $debug ? $io->text('-->SummonerId: ' . $info['id']) : null;

            $summoner->setAccountId($info['accountId']);
            $summoner->setSummonerId($info['id']);
            $summoner->setName($info['name']);

            $this->em->persist($summoner);

            try {
                $this->em->flush();
                $debug ? $io->text('Flushing DB: <fg=green>Success</>') : null;
            } catch (\Exception $e) {
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }

            $debug ? $io->text('-------------------------------------------------------') : null;

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
    }
}
