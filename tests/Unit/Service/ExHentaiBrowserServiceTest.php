<?php
namespace App\Tests\Service;

use App\Entity\ExhentaiArchiverKey;
use App\Entity\ExhentaiGallery;
use App\Service\ExHentaiBrowserService;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ExHentaiBrowserServiceTest extends TestCase
{
    const API_GALLERY_RESPONSE_SUCCESS = '{"gmetadata": [{"gid": 618395,"token": "0439fa3666","archiver_key": "403565--d887c6dfe8aae79ed0071551aa1bafeb4a5ee361","title": "(Kouroumu 8) [Handful☆Happiness! (Fuyuki Nanahara)] TOUHOU GUNMANIA A2 (Touhou Project)","title_jpn": "(紅楼夢8) [Handful☆Happiness! (七原冬雪)] TOUHOU GUNMANIA A2 (東方Project)","category": "Non-H","thumb": "https://ehgt.org/14/63/1463dfbc16847c9ebef92c46a90e21ca881b2a12-1729712-4271-6032-jpg_l.jpg","uploader": "avexotsukaai","posted": "1376143500","filecount": "20","filesize": 51210504,"expunged": false,"rating": "4.43","torrentcount": "0","tags": ["parody:touhou project","group:handful happiness","artist:nanahra fuyuki","full color","artbook"]}]}';

    /**
     * @var MockObject|Client
     */
    private $client;

    /**
     * @var ExHentaiBrowserService
     */
    private $browser;

    /**
     * @var MockObject|EntityManager
     */
    private $entityManager;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->entityManager = $entitymanager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()->getMock();

        $this->logger = $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->browser = new ExHentaiBrowserService('passhash',123, $entitymanager, $logger);
        $this->browser->setClient($this->client);
        $this->browser->rateLimiterEnabled = false;

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    /**
     * @test
     */
    public function willCreateCookieJarOnConstruct()
    {
        $cookieJar = $this->browser->getCookieJar();
        $this->assertEquals('passhash', $cookieJar->getCookieByName('ipb_pass_hash')->getValue());
        $this->assertEquals(123, $cookieJar->getCookieByName('ipb_member_id')->getValue());
    }

    /**
     * @test
     */
    public function willGetGalleriesFromListIndex()
    {
        $this->createOverviewTest('/',file_get_contents(__DIR__.'/../../stubs/e-hentai-index-list.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->getIndex();

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     */
    public function willGetGalleriesFromThumbnailViewIndex()
    {
        $this->createOverviewTest('/',file_get_contents(__DIR__.'/../../stubs/e-hentai-index-thumbs.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->getIndex();

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function willThrowExceptionIfNoGalleriesWereParsed()
    {
        $this->client->expects($this->at(0))
            ->method('request')
            ->with(
                $this->stringContains('GET'),
                $this->stringContains('/', false),
                $this->anything()
            )
            ->willReturn(new Response(200));

        $this->browser->getIndex();
    }

    /**
     * @test
     */
    public function willSearchForTags()
    {
        $this->createOverviewTest('female%3Amilf', file_get_contents(__DIR__.'/../../stubs/e-hentai-index-thumbs.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->getByTag('female:milf');

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     */
    public function willDownloadGalleryAsZip()
    {
        $gallery = (new ExhentaiGallery())
            ->setId(1)
            ->setToken('abc123')
            ->setArchiverKey((new ExhentaiArchiverKey('test')));

        $this->setSuccesfullZipDownloadExpects();

        $this->browser->downloadGallery($gallery);
    }

    /**
     * @test
     */
    public function downloadWillUpdateArchiveTokenIfExpired()
    {
        $gallery = (new ExhentaiGallery())
            ->setId(1)
            ->setToken('abc123')
            ->setArchiverKey((new ExhentaiArchiverKey('test'))->setTime(new \DateTime('-2 days')));

        $jsonResponse = "{\"gmetadata\":[{\"gid\":1,\"token\":\"a\",\"archiver_key\":\"test\",\"title\":\"meh\",\"title_jpn\":\"meh\",\"category\":\"Doujinshi\",\"thumb\":\"https:\/\/ehgt.org\/6b\/8d\/6b8d-1-1480-2093-png_l.jpg\",\"uploader\":\"username\",\"posted\":\"1538769279\",\"filecount\":\"35\",\"filesize\":2,\"expunged\":false,\"rating\":\"4.23\",\"torrentcount\":\"1\",\"tags\":[\"language:english\",\"language:translated\"]}]}";

        $this->client->expects($this->at(0))
            ->method('request')
            ->with(
                $this->equalTo('POST')
            )
            ->willReturn(
                new Response(
                    200,
                    [],
                    $jsonResponse
                ));

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(ExhentaiGallery::class))
            ->willReturn($gallery);

        $this->setSuccesfullZipDownloadExpects(1);

        $this->browser->downloadGallery($gallery);

        $this->assertTrue($gallery->getArchiverKey()->getTime() > new \DateTime('-1 day'));
    }

    /**
     * @test
     */
    public function willSearch()
    {
        $this->createOverviewTest('test', file_get_contents(__DIR__.'/../../stubs/e-hentai-index-thumbs.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->search('test');

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     */
    public function willSearchTagIfTagIsGivenToSearch()
    {
        $this->createOverviewTest('female%3A"big breasts$"', file_get_contents(__DIR__.'/../../stubs/e-hentai-index-thumbs.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->search('female:big breasts');

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     */
    public function willSearchForTagsWithSpacesInTagName()
    {
        $this->createOverviewTest('female%3A"big breasts$"', file_get_contents(__DIR__.'/../../stubs/e-hentai-index-thumbs.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->getByTag('female:big breasts');

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     */
    public function willSearchRemote()
    {
        $this->createOverviewTest('test', file_get_contents(__DIR__.'/../../stubs/e-hentai-index-thumbs.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->searchRemote('test');

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     */
    public function willUseTagAsSearchIfSearchRemoteIsCalledWithATagQuery()
    {
        $this->createOverviewTest('female%3Amilf$', file_get_contents(__DIR__ . '/../../stubs/e-hentai-index-thumbs.html'));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->searchRemote('female:milf');

        $this->assertTrue(is_array($result));
        $this->assertInstanceOf(ExhentaiGallery::class, $result[0]);
    }

    /**
     * @test
     */
    public function willReturnSingleGallery()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with(
                $this->stringContains('POST'),
                $this->stringContains('api.php'),
                $this->anything()
            )
            ->willReturn(new Response(200, [], self::API_GALLERY_RESPONSE_SUCCESS));

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(
                $this->stringContains(ExhentaiGallery::class)
            )
            ->willReturn(new ExhentaiGallery());

        $result = $this->browser->getGallery(1, 'abc');

        $this->assertInstanceOf(ExhentaiGallery::class, $result);
    }

    /**
     * @test
     */
    public function willRateLimitRequestsAtFivePerFiveSecond()
    {
        // Enable rate limit
        $this->browser->rateLimiterEnabled = true;
        $timeStart = time();

        $this->client->expects($this->exactly(5))
            ->method('request')
            ->with(
                $this->stringContains('GET'),
                $this->stringContains('/'),
                $this->isType('array')
            )
            ->willReturn(new Response());

        for ($i = 0; $i <= 4; $i++) {
            if ($i == 3) {
                $lastUnLimitedCall = time();
            }
            $this->browser->request('GET', '/');
        }

        $timeEnd = time();

        $this->assertTrue(($lastUnLimitedCall - $timeStart) < 5);
        $this->assertTrue(($timeEnd - $timeStart) >= 5);
    }

    public function testGetters()
    {
        $this->assertEquals($this->client, $this->browser->getClient());
        $this->assertTrue(is_array($this->browser->getHistory()));
    }

    public function testConstruct()
    {
        $browser = new ExHentaiBrowserService('passwordhash', 39, $this->entityManager, $this->logger);

        $cookieJar = $browser->getCookieJar();

        $this->assertEquals(39, $cookieJar->getCookieByName('ipb_member_id')->getValue());
        $this->assertEquals('passwordhash', $cookieJar->getCookieByName('ipb_pass_hash')->getValue());
    }

    /**
     * @test
     */
    public function logoutWillClearCookieJar()
    {
        $this->browser->logout();

        $this->assertEquals(0, $this->browser->getCookieJar()->count());
    }

    private function createOverviewTest($uri, $html)
    {
        $apiRequests = ceil(230/25);

        $this->client->expects($this->at(0))
            ->method('request')
            ->with(
                $this->stringContains('GET'),
                $this->stringContains($uri, false),
                $this->anything()
            )
            ->willReturn(new Response(200,[],$html));

        for ($i = 1; $i <= $apiRequests; $i++) {
            $this->client->expects($this->at($i))
                ->method('request')
                ->with(
                    $this->stringContains('POST'),
                    $this->stringContains('api.php'),
                    $this->anything()
                )
                ->willReturn(new Response(200, [], self::API_GALLERY_RESPONSE_SUCCESS));
        }
    }

    private function setSuccesfullZipDownloadExpects($offset = 0)
    {


        $this->client->expects($this->at($offset))
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->stringContains('archiver.php?gid=1&token=abc123&or=test')
            )
            ->willReturn(new Response(200, [], file_get_contents(__DIR__.'/../../stubs/download-gallery/step-1.html')));

        $this->client->expects($this->at($offset+1))
            ->method('request')
            ->with(
                $this->equalTo('POST'),
                $this->stringContains('archiver.php?gid=1&token=abc123&or=test'),
                $this->identicalTo([
                    'form_params' => [
                        'dltype'  => 'org',
                        'dlcheck' => 'Download Original Archive'
                    ]
                ])
            )
            ->willReturn(new Response(200, [], file_get_contents(__DIR__.'/../../stubs/download-gallery/step-2.html')));

        $this->client->expects($this->at($offset+2))
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->stringStartsWith('http://0.0.0.0/archive/1/1cf33f5ef9ed1fc1bf958ae7ecdff04546b088ad/58w6ctf95t9/0')
            )
            ->willReturn(new Response(200, ['host' => "0.0.0.0"], file_get_contents(__DIR__.'/../../stubs/download-gallery/step-3.html')));

        $this->client->expects($this->at($offset+3))
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('http://0.0.0.0/archive/1/1cf33f5ef9ed1fc1bf958ae7ecdff04546b088ad/58w6ctf95t9/0?start=1'),
                $this->arrayHasKey('save_to')
            )
            ->willReturn(new Response(200, [], file_get_contents(__DIR__.'/../../stubs/download-gallery/step-3.html')));
    }
}
