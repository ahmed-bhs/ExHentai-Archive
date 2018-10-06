<?php

namespace App\Command;

use App\Entity\ExhentaiGallery;
use Elastica\Query\Match;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class HentaiScanLocalCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'hentai:scan-local';
    protected $zipArchive;

    public function __construct(?string $name = null)
    {
        $this->zipArchive = new \ZipArchive();
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Scans the given directory for Gallery zipfiles')
            ->addArgument('path', InputArgument::REQUIRED, 'Starting directory')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $elasticaFinder = $this->getContainer()->get('fos_elastica.finder.app.gallery');

        $io = new SymfonyStyle($input, $output);
        $directoryPath = $input->getArgument('path');

        $filesystem = new Filesystem();
        $finder = new Finder();
        $hit = $filemiss = $sizemiss = $countmiss = 0;

        if($filesystem->exists($directoryPath)) {
            $inodes = [];
            $finder->files()->name('*.zip')->in($directoryPath)->filter(function (\SplFileInfo $file) use ($inodes) {
                if(!in_array($file->getInode(), $inodes)) {
                    $inodes[] = $file->getInode();
                    return true;
                }
                return false;
            });

            $io->note(sprintf('Found %d files', $finder->count()));

            foreach($finder as $file) {
                $match = false;
                $zipInfo = $this->getZipInfo($file);

                $searchName = substr($file->getFilename(), 0,-4);

                // @todo put elastica logic in a service
                $fieldQuery = new Match();
                $fieldQuery->setFieldQuery('title', $searchName);
                $pagination = $elasticaFinder->findPaginated($fieldQuery);
                $results = $pagination->getCurrentPageResults();
                /** @var ExhentaiGallery $result */
                foreach($results as $result) {
                    $filteredName = str_replace(['?','|', '\'','"','~'],' ', $result->getTitle());

                    if($searchName == $filteredName) {
                        if($zipInfo['files'] == $result->getFileCount()) {
                            if($zipInfo['contsize'] == $result->getFilesize()) {
                                $match = true;
                                $hit++;
                                $io->success(sprintf('100%% match on %s', $result->getTitle()));
                                break; // Exit out of loop since we're done here.
                            } else {
                                $sizemiss++;
                                $io->note(sprintf(
                                    'Name match but filesize mismatch on %s. %d - %d',
                                    $result->getTitle(),
                                    $zipInfo['contsize'],
                                    $result->getFilesize()
                                ));
                            }
                        } else {
                            $countmiss++;
                            $io->note(sprintf(
                                'Name match but imagecount mismatch on %s. %d - %d',
                                $result->getTitle(),
                                $zipInfo['files'],
                                $result->getFileCount()
                            ));
                        }
                    }
                }

                if(!$match) {
                    $filemiss++;
                }

                unset($results, $pagination, $fieldQuery);
            }

            $io->note(sprintf(
                "HIT: %d FILEMISS: %d COUNTMISS: %d SIZEMISS: %d",
                $hit,
                $filemiss,
                $countmiss,
                $sizemiss
            ));
            $io->success(sprintf('Finished scanning local files. Found %d files of which %d were matches', $finder->count(), $hit));
        } else {
            $io->error('Directory not found or readable');
        }

    }

    private function getZipInfo(SplFileInfo $fileInfo)
    {
        $this->zipArchive->open($fileInfo->getPathname());

        $contentSize = 0;
        $compSize = 0;
        for($i=0;$i<=$this->zipArchive->numFiles;$i++)
        {
            $stat = $this->zipArchive->statIndex($i);
            $contentSize = $contentSize + $stat['size'];
            $compSize    = $compSize + $stat['comp_size'];
        }

        $return = [
            'fileName' => $fileInfo->getFilename(),
            'files'    => $this->zipArchive->numFiles,
            'size'     => $fileInfo->getSize(),
            'contsize' => $contentSize, // THIS VALUE SHOULD MATCH WITH API
            'compsize' => $compSize,
        ];

        $this->zipArchive->close();

        return $return;
    }
}
