<?php

namespace App\Command;

use App\Model\GalleryToken;
use Doctrine\DBAL\FetchMode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class ExHentaiMigrateDBCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'exhentai:migrate-db';

    private $counter = 0;
    private $total = 0;

    protected function configure()
    {
        $this->setDescription('Add a short description for your command')
            ->addOption('am-fork', 'f', InputOption::VALUE_NONE, 'Do the work of a fork');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if($input->getOption('am-fork')) {
            $dbConn = $this->getContainer()->get('database_connection');

            $query = $dbConn->query('SELECT exhenid, hash FROM gallery WHERE archived = 1 AND download = 1');
            $query->execute();
            $dbRows = $query->fetchAll(FetchMode::ASSOCIATIVE);
            // Queued
            $query2 = $dbConn->query('SELECT exhenid, hash FROM gallery where (archived = 0 and download = 1) or hasmeta = 0');
            $query2->execute();
            $dbRows2 = $query2->fetchAll(FetchMode::ASSOCIATIVE);

            $this->total = count($dbRows) + count($dbRows2);

            $io->note(sprintf('Looking up %d galleries from API', count($dbRows)));
            $this->processGallerySet($dbRows, 2, $io);
            $dbConn->query('UPDATE exhentai_gallery SET  download_state = 2 WHERE id > 1')->execute();
            $io->note(sprintf('Looking up %d galleries from API', count($dbRows2)));
            $this->processGallerySet($dbRows2, 0, $io);

            $io->success('Imported ' . $this->total . ' galleries');
            return 0;
        } else {
            $process = new Process(['php', 'bin/console', 'exhentai:migrate-db', '--am-fork', '--no-debug'], realpath($this->getContainer()->get('kernel')->getRootDir().'/../'));
            $process->setTimeout(0.0);
            $io->note('Creating fork');

            $i = 0;
            $maxRuns = 200;
            while(true) {
                $io->note('Restarting fork');
                $process->run(function($type, $buffer) use ($process, $io) {
                    $buffer = trim($buffer);
                    if(!empty($buffer)) {
                        if (Process::ERR === $type)
                            $io->error('FROM FORK: '. $buffer);
                        else
                            $io->note('FROM FORK: '. $buffer);
                    }
                });

                if($process->isSuccessful())
                    break;

                if($i == $maxRuns)
                    throw new \Exception('MIGRATION FAILED. PROCESS EXCEEDED MAX RUNS');

                $i++;
            }

            $io->success('MIGRATION COMPLETED');
        }
    }

    private function processGallerySet(array $tokens, int $downloadState, SymfonyStyle $io)
    {
        // Filter existing galleries
        $existingIds = $this->getContainer()->get('database_connection')->query('SELECT id FROM exhentai_gallery');
        $existingIds->execute();
        $existingIds = $existingIds->fetchAll(FetchMode::ASSOCIATIVE);
        $existingIds = array_map(function($item) {
            return $item['id'];
        }, $existingIds);

        $converted = array_map(function($item) {
            return new GalleryToken($item['exhenid'], $item['hash']);
        }, $tokens);

        $filtered = array_filter($converted, function($item) use ($existingIds) {
            return !in_array($item->getId(), $existingIds);
        });
        $this->counter = count($existingIds);

        $i=0;
        while(count($filtered)) {
            $i++;
            $batch = array_splice($filtered, 0, 25);
            
            $galleries = $this->getContainer()->get('exhentai.client')->getGalleries($batch);

            $this->counter = $this->counter+count($galleries);
            $io->note(sprintf('[%d/%d]', $this->counter, $this->total));
            unset($galleries);
            if(($i % 5) == 0) {
                $io->note(sprintf('[%d/%d] MEMORY CURRENT: %s PEAK: %s',$this->counter, $this->total, $this->convert(memory_get_usage(true)), $this->convert(memory_get_peak_usage(true))));
                $io->comment('GC COLLECT');
                $this->getContainer()->get('doctrine.orm.entity_manager')->clear();
                gc_collect_cycles();
                gc_mem_caches();
            }
        }
    }

    private function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
}
