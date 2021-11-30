<?php

namespace App\Command;

use App\Entity\Summoner;
use App\Utils\LS\Crawl;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use PHPUnit\Runner\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateSummonerCommand extends Command
{
    protected static $defaultName = 'app:update-summoner';
    protected static $defaultDescription = 'Update Puuid for Summoner w/out one';

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update Puuid for Summoner w/out one');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $summoners = $this->em->getRepository(Summoner::class)->findBy(array(
            'puuid' => null
        ));

        if (count($summoners) > 0) {

            $lsCrawl = new Crawl($this->em);

            foreach ($summoners as $summoner) {

                $details = $lsCrawl->getSummonerDetails($summoner);

                $summoner->setPuuid($details['puuid']);
                $this->em->persist($summoner);
                $io->info('Updated '.$summoner->getName());

                try {
                    $this->em->flush();
                } catch (Exception $e) {
                    $io->error('MYSQL ERROR: ' . $e->getMessage());
                }

            }


        } else {
            $io->success('No missing puuids found');
        }

        return Command::SUCCESS;
    }
}
