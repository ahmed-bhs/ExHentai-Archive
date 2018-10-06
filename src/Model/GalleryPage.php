<?php
namespace App\Model;

use App\Entity\ExhentaiGallery;
use App\Service\ExHentaiBrowserService;
use Symfony\Component\DomCrawler\Crawler;

class GalleryPage extends ExhentaiGallery
{
    public $dailyBonus;
    public $hentaiVerseChallenge;

    public function hasDailyBonus(): bool
    {
        return (bool)$this->dailyBonus;
    }

    public function hasHentaiVerseChallenge(): bool
    {
        return (bool)$this->hentaiVerseChallenge;
    }

    public function hasEvent(): bool
    {
        return $this->hasDailyBonus() || $this->hasHentaiVerseChallenge();
    }

    /**
     * @param string $html
     * @return GalleryPage
     */
    public static function fromHtml(string $html)
    {
        $crawler = new Crawler($html);

        return self::fromCrawler($crawler);
    }

    /**
     * @param Crawler $document
     * @return GalleryPage
     */
    public static function fromCrawler(Crawler $document)
    {
        $model = new self;

        $model->setTitle($document->filterXPath("//h1[@id='gn']")->text());
        $model->setTitleJapan($document->filterXPath("//h1[@id='gj']")->text());

        $eventpaneNode = $document->filterXPath("//div[@id='eventpane']");
        // Check if the page contains an eventpane (hentaiverse event or daily bonus)
        if($eventpaneNode->count()) {
            // Check if we have a daily bonus or hentai verse event
            try {
                $model->hentaiVerseChallenge = $eventpaneNode->filter('div > a')->last()->attr('href');
            } catch (\InvalidArgumentException $exception) { }

            try {
                if(strpos($eventpaneNode->filter('p')->first()->text(), 'dawn of a new day') !== FALSE) {
                    $rewards = $eventpaneNode->filter('p')->last();

                    $model->dailyBonus = [
                        'experience' => (int)str_replace(',','',$rewards->filter('strong')->first()->text()),
                        'credits'    => (int)str_replace(',','',$rewards->filter('strong')->eq(1)->text()),
                        'gp'         => (int)str_replace(',','',$rewards->filter('strong')->eq(2)->text()),
                        'hath'       => (int)str_replace(',','',$rewards->filter('strong')->last()->text()),
                    ];

                }
            } catch (\InvalidArgumentException $exception) { }
        }

        return $model;
    }
}
