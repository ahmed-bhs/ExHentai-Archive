<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExhentaiAuditCommand extends Command
{
    protected static $defaultName = 'exhentai:audit';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force audit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $force =$input->getOption('force');

        $galleries =


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
