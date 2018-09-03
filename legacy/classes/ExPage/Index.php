<?php

class ExPage_Index extends ExPage_Abstract
{
    public function isLastPage()
    {
        return (count($this->findElement('td.ptds + td.ptdd')) > 0);
    }

    public function getGalleries()
    {
        $ret = array();

        $links = $this->findElement('td.itd .it5 a');
        /** @var \DOMElement $linkElem */
        foreach ($links as $linkElem) {
            $gallery = new stdClass();

            $gallery->name = $linkElem->textContent;

            preg_match("~https://e(?:x|-)hentai.org/g/(\d*)/(\w*)/~", $linkElem->getAttribute('href'), $matches);

            if (isset($matches[1]) && isset($matches[2])) {
                $gallery->exhenid = $matches[1];
                $gallery->hash = $matches[2];

                $ret[] = $gallery;
            }
        }

        return $ret;
    }
}
