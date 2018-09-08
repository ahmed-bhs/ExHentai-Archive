<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExhentaiScrapeCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'exhentai:scrape';

    protected function configure()
    {
        $this
            ->setDescription('Will collect galleries from ExHentai')
            ->addOption('tag', 't', InputArgument::OPTIONAL, 'Tag to search for', null)
            ->addOption('page', 'p', InputOption::VALUE_OPTIONAL, 'Page offset, start from page', 0)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $page = $input->getOption('page');
        $tag = $input->getOption('tag');
        $browser = $this->getContainer()->get('exhentai.client');

        $params = [
            'page' => 0
        ];

        // Get total pages
        if($tag) {
            $params['f_search'] = $browser->getTagSearchQuery($tag);
            $indexHtml = $browser->get('/', $params);
        } else {
            $indexHtml = $browser->get('/', $params);
        }

        if(preg_match('~hentai\.org\/\?page=\'\+Math\.min\(([0-9]+),~',$indexHtml, $matches)) {
            $totalPage = (int)$matches[1];
        } else {
            $io->error('Unable to get pagecount');
            return 2;
        }

        if($page > $totalPage) {
            $io->error('Page exceeds total amount of pages available');
            3;
        }

        for($page;$page <= $totalPage; $page++) {
            $io->note(sprintf('Retrieving page %d out of %d',$page, $totalPage));
            $browser->getByTag($tag, $page);
        }

        $io->success('Scrape complete');
    }
}
