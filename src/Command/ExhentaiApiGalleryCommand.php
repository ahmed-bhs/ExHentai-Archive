<?php

namespace App\Command;

use App\Entity\ExhentaiGallery;
use App\Service\ExHentaiBrowserService;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExhentaiApiGalleryCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'hentai:api:gallery';

    protected $browser;

    public function __construct(?string $name = null, ExHentaiBrowserService $exhentaiBrowser)
    {
        $this->browser = $exhentaiBrowser;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Lookup galleries from e-hentai API. USE the ID:TOKEN as input format. Use of multiple galleries supported')
            ->addArgument('query', InputArgument::OPTIONAL, 'galleries to lookup (ID:TOKEN FORMAT)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $query = $input->getArgument('query');

        try {
            if ($query) {
                $io->note(sprintf('Searching for %s', $query));
                $galleries = $this->browser->search($query);
            } else {
                $galleries = $this->browser->getIndex();
            }

            /** @var ExhentaiGallery $gallery */
            foreach ($galleries as $gallery) {
                $io->note(sprintf('Category: %s | Name: %s', $gallery->getCategory()->getTitle(), $gallery->getTitle()));
            }

            $io->success(sprintf('Complete with %d results', count($galleries)));
        } catch (TooManyRedirectsException $exception) {
            $io->error('FATAL: '.$exception->getMessage());

            $history = $this->browser->getHistory();
            foreach($history as $transaction) {
                if($transaction['response']) {
                    $io->caution(sprintf(
                        '[%s] %s -> %s',
                        $transaction['request']->getMethod(),
                        (string)$transaction['request']->getUri(),
                        $transaction['response']->getStatusCode()
                    ));
                }
            }
        }
//        $io->note($this->browser->getClient()->getInternalRequest()->getUri());
    }
}
