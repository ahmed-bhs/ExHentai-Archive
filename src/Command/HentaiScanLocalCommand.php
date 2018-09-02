<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class HentaiScanLocalCommand extends Command
{
    protected static $defaultName = 'hentai:scan-local';

    protected function configure()
    {
        $this
            ->setDescription('Scans the given directory for Gallery zipfiles')
            ->addArgument('path', InputArgument::REQUIRED, 'Starting directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        require_once __DIR__.'/../../legacy/common.php';

        $io = new SymfonyStyle($input, $output);
        $directoryPath = $input->getArgument('path');

        $filesystem = new Filesystem();
        $finder = new Finder();

        if($filesystem->exists($directoryPath)) {
            $io->note('Loading galleries');
            // Get unarchived galleries
            $galleries = \R::find('gallery','((archived = 0 and download = 1) or hasmeta = 0) and deleted = 0 and source = 0');
            $galleryNames = [];
            foreach($galleries as $gallery) {
                $galleryNames[$gallery->id] = [
                    $gallery->name,
                    $gallery->origtitle
                ];
            }
            unset($galleries);

            $io->note(sprintf('Loaded %d galleries without archives', count($galleryNames)));
            $io->note(sprintf('Scanning directory: %s', $directoryPath));
            $inodes = [];
            $finder->files()->name('*.zip')->in($directoryPath)->filter(function (\SplFileInfo $file) use ($inodes) {
                if(!in_array($file->getInode(), $inodes)) {
                    $inodes[] = $file->getInode();
                    return true;
                }
                return fakse;
            });

            $io->note(sprintf('Found %d files', $finder->count()));

            /** @var SplFileInfo $file */
            foreach($finder as $file) {
                var_dump($file);die();
            }


        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
