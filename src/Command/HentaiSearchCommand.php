<?php

namespace App\Command;

use App\Entity\ExhentaiGallery;
use Elastica\Query\Match;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HentaiSearchCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'hentai:search';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('searchquery', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $query = $input->getArgument('searchquery');

        if ($query) {
            $io->note(sprintf('You passed an argument: %s', $query));
        }

        $finder = $this->getContainer()->get('fos_elastica.finder.app.gallery');

        $fieldQuery = new Match();
        $fieldQuery->setFieldQuery('title', $query);

//        $results = $finder->find($fieldQuery);

        $paginator = $finder->findPaginated($fieldQuery);
        $resultCount = $paginator->getNbResults();

        $results = $paginator->getCurrentPageResults();
        /** @var ExhentaiGallery $result */
        foreach($results as $result)
        {
            $filteredName = str_replace(['?','|', '\'','"','~'],' ', $result->getTitle());

            if($query == $filteredName) {
                $io->success('FOUND MATCH ON ELASTIC');
            }

            $io->note(sprintf(
                'Match result: %d-%d - %s |  Name: %s | Filtered: %s',
                strcmp($query, $filteredName),
                strcmp($result->getTitle(), $query),
                ($query == $filteredName) ? 'true':'false',
                $result->getTitle(),
                $filteredName
            ));
        }

        $io->success(sprintf('FOUND %d RESULTS %d', $resultCount, count($results)));
    }
}
