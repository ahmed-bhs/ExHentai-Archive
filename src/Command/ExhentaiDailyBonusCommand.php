<?php

namespace App\Command;

use App\Model\GalleryPage;
use App\Service\ExHentaiBrowserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExhentaiDailyBonusCommand extends Command
{
    protected static $defaultName = 'exhentai:daily-bonus';

    /**
     * @var ExHentaiBrowserService
     */
    private $browserService;

    public function __construct(?string $name = null, ExHentaiBrowserService $browserService = null)
    {
        $this->browserService = $browserService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Open a random gallery to get the daily bonus if you\'re a donator');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $galleries = $this->browserService->getIndex();

        if (!$galleries || !is_array($galleries) || empty($galleries)) {
            $io->error('Unable to find any galleries');
            return 2;
        }

        $success = false;
        $data = [];

        for($i=0; $i<3;$i++) {
            $galleryHtml = $this->browserService->get(sprintf(
                '%sg/%s/%s/',
                ExHentaiBrowserService::SAFE_URL,
                $galleries[$i]->getId(),
                $galleries[$i]->getToken()
            ));

            $gallery = GalleryPage::fromHtml($galleryHtml);

            $success = $gallery->hasDailyBonus();
            if($success) {
                $data = $gallery->dailyBonus;
                continue;
            }
        }

        if($success) {
            $io->success(sprintf(
                'Collected %d EXP, %d Credits, %d GP and %d Hath',
                $data['experience'],
                $data['credits'],
                $data['gp'],
                $data['hath']
            ));
            return 0;
        } else {
            $io->error('Did not get any bonuses. Did you collect already today?');
            return 1;
        }


    }
}
