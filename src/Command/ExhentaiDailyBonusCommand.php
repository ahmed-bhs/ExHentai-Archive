<?php

namespace App\Command;

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

        $galleryHtml = $this->browserService->get(sprintf(
            '%sg/%s/%s/',
            ExHentaiBrowserService::SAFE_URL,
            $galleries[0]->getId(),
            $galleries[0]->getToken()
        ));

        if (preg_match('~<div\sid=\"eventpane\"(?:[^>]*?)><p(?:[^>]*?)>It is the dawn of a new day!</p>.*?<strong>([0-9,]+)</strong> EXP, <strong>([0-9,]+)</strong> Credits, <strong>([0-9,]+)</strong> GP and <strong>([0-9,]+)</strong> Hath~', $galleryHtml, $matches)) {
            $io->success(sprintf(
                'Collected %d EXP, %d Credits, %d GP and %d Hath',
                $matches[1],
                $matches[2],
                $matches[3],
                $matches[4]
            ));

            return 0;
        } else {
            $io->error('Did not get any bonuses. Did you collect already today?');
            return 1;
        }
    }
}
