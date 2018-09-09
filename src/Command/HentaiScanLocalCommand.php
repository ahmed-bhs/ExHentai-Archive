<?php

namespace App\Command;

use Doctrine\DBAL\FetchMode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class HentaiScanLocalCommand extends ContainerAwareCommand
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
//        require_once __DIR__.'/../../legacy/common.php';

        $io = new SymfonyStyle($input, $output);
        $directoryPath = $input->getArgument('path');

        $filesystem = new Filesystem();
        $finder = new Finder();

        if($filesystem->exists($directoryPath)) {
            $galleryNames = [];
            $io->note('Loading galleries');
            // Get unarchived galleries
            $db = $this->getContainer()->get('database_connection')->query('SELECT id, title, title_japan, file_count, filesize FROM exhentai_gallery');
            $db->execute();
            $galleries = $db->fetchAll(FetchMode::ASSOCIATIVE);
            array_walk($galleries, function($item, $key) use (&$galleryNames) {
                $galleryNames[$item['id']] = [
                    'title'            => $item['title'],
                    'title_japan'      => $item['title_japan'],
                    'normalized_title' => str_replace(['|',':'], '', $item['title']),
                    'file_size'        => $item['filesize'],
                    'file_count'       => $item['file_count']
                ];
            });

            $io->note(sprintf('Loaded %d galleries without archives', count($galleryNames)));
            $io->note(sprintf('Scanning directory: %s', $directoryPath));
            $inodes = [];
            $finder->files()->name('*.zip')->in($directoryPath)->filter(function (\SplFileInfo $file) use (&$inodes) {
                if(!in_array($file->getInode(), $inodes)) {
                    $inodes[] = $file->getInode();
                    return true;
                }
                return false;
            });

            $io->note(sprintf('Found %d files', $finder->count()));

            /** @var SplFileInfo $file */
            foreach($finder as $file) {
                $finfo = $this->getZipInfo($file);
                foreach($galleryNames as $galleryName) {
                    
                }
            }

            die();


        } else {
            $io->error('Directory not found');
            return 1;
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }

    private function getZipInfo(SplFileInfo $fileInfo)
    {
        $zipArchive = new \ZipArchive();
        $zipArchive->open($fileInfo->getPathname());

        $contentSize = 0;
        $compSize = 0;
        for($i=0;$i<=$zipArchive->numFiles;$i++)
        {
            $stat = $zipArchive->statIndex($i);
            $contentSize = $contentSize + $stat['size'];
            $compSize    = $compSize + $stat['comp_size'];
        }

        $return = [
            'fileName' => $fileInfo->getFilename(),
            'files'    => $zipArchive->numFiles,
            'size'     => $fileInfo->getSize(),
            'contsize' => $contentSize, // THIS VALUE SHOULD MATCH WITH API
            'compsize' => $compSize,
        ];

        return $return;
    }
}
