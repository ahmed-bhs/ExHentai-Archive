<?php

namespace App\Command;

use App\Entity\ExhentaiGallery;
use App\Model\GalleryToken;
use App\Service\ExHentaiBrowserService;
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
            ->addArgument('galleries', InputArgument::IS_ARRAY, 'galleries to lookup (ID:TOKEN FORMAT)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $galleries = $input->getArgument('galleries');

        if ($galleries){
            $io->note(sprintf('Looking up %d galleries', count($galleries)));
        }

        $tokenList = [];
        foreach($galleries as $gallery) {
            $explode = explode(':', $gallery);
            $tokenList[] = new GalleryToken($explode[0], $explode[1]);
        }

        $galleries = $this->browser->getGalleries($tokenList);

        /** @var ExhentaiGallery $gallery */
        foreach($galleries as $gallery)
        {
            $io->note(sprintf('Category: %s | Name: %s', $gallery->getCategory()->getTitle(), $gallery->getTitle()));
        }

        $io->success('Complete');
    }
}
