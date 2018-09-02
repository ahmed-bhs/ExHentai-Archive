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
        $this->client = new ExClient();

        $indexPage = new ExPage_Index($this->client->index());
        $galleries = $indexPage->getGalleries();

        if(count($galleries) > 0) {
            foreach($galleries as $gallery) {
                $galleryHtml = $this->client->gallery($gallery->id, $gallery->hash);
            }
        } else {
            Log::error(self::LOG_TAG, 'No galleries found');
        }
    }
}
