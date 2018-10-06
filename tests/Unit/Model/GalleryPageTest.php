<?php
/**
 * Created by PhpStorm.
 * User: PBX_g33k
 * Date: 26/09/2018
 * Time: 22:51
 */

namespace App\Tests\Model;

use App\Model\GalleryPage;
use PHPUnit\Framework\TestCase;

class GalleryPageTest extends TestCase
{
    /**
     * @test
     */
    public function willReturnFalseOnHasEventIfNoEventPaneIsShown()
    {
        $galleryModel = GalleryPage::fromhtml(file_get_contents(__DIR__.'/../../stubs/gallery-single.html'));

        $this->assertFalse($galleryModel->hasEvent());
        $this->assertFalse($galleryModel->hasHentaiVerseChallenge());
        $this->assertFalse($galleryModel->hasDailyBonus());
    }

    /**
     * @test
     */
    public function willDetectIfPageHasHentaiVerseEvent()
    {
        $galleryModel = GalleryPage::fromhtml(file_get_contents(__DIR__.'/../../stubs/gallery-single-hentaiverse-challenge.html'));

        $this->assertTrue($galleryModel->hasEvent());
        $this->assertTrue($galleryModel->hasHentaiVerseChallenge());
        $this->assertFalse($galleryModel->hasDailyBonus());
    }

    /**
     * @test
     */
    public function willDetectIfPageHasDailyBonus()
    {
        $galleryModel = GalleryPage::fromhtml(file_get_contents(__DIR__.'/../../stubs/gallery-single-dawn-of-new-day.html'));

        $this->assertTrue($galleryModel->hasEvent());
        $this->assertTrue($galleryModel->hasDailyBonus());
        $this->assertFalse($galleryModel->hasHentaiVerseChallenge());
        $this->assertEquals(104946, $galleryModel->dailyBonus['experience']);
        $this->assertEquals(10010, $galleryModel->dailyBonus['credits']);
        $this->assertEquals(10000, $galleryModel->dailyBonus['gp']);
        $this->assertEquals(8, $galleryModel->dailyBonus['hath']);
    }

    public function testFromHtml()
    {
        $gallery = GalleryPage::fromHtml(file_get_contents(__DIR__.'/../../stubs/gallery-single.html'));

        $this->assertSame('(C73) [PAM Kikakushitsu (Tamura Chii)] Heart Full (Kodomo no Jikan)', $gallery->getTitle());
        $this->assertSame('(C73) [PAM★企画室 (田村ちい)] はあと☆ふる (こどものじかん)', $gallery->getTitleJapan());
    }
}
