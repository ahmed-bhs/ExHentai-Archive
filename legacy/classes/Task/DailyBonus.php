<?php

class Task_DailyBonus extends Task_Abstract
{
    const LOG_TAG = 'DailyBonus';

    /**
     * @var ExClient
     */
    private $client;

    public function run($options = array())
    {
        $this->client = new ExClient(true);

        $indexPage = new ExPage_Index($this->client->index());
        $galleries = $indexPage->getGalleries();

        if(count($galleries) > 0) {
            foreach($galleries as $gallery) {
                $galleryHtml = $this->client->gallery($gallery->exhenid, $gallery->hash);
                if(preg_match('~<div\sid=\"eventpane\"(?:[^>]*?)><p(?:[^>]*?)>It is the dawn of a new day!</p>~', $galleryHtml)) {
                    Log::debug(self::LOG_TAG, 'DONE');
                } else {
                    Log::error(self::LOG_TAG, "Couldn't find the dawn of the day message. Perhaps already opened a gallery today?");
                    exit;
                }
            }
        } else {
            Log::error(self::LOG_TAG, 'No galleries found');
        }
    }
}
