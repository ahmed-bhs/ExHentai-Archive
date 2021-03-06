<?php
namespace App\Service;

use App\Entity\ExhentaiGallery;
use App\Model\GalleryToken;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class ExHentaiBrowserService
{
    const BASE_URL       = 'https://exhentai.org/';
    const SAFE_URL       = 'https://e-hentai.org/';
    const API_URL        = 'https://api.e-hentai.org/api.php';
    const USER_AGENT     = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';

    /**
     * @var array
     */
    private $guzzleContainer = [];

    public $rateLimiterEnabled = true;

    /**
     * @var Middleware
     */
    private $history;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CookieJar
     */
    private $cookieJar = null;

    /**
     * @var \DateTimeInterface
     */
    private $lastRequest;

    /**
     * @var ResponseInterface
     */
    private $lastResponse;

    /**
     * @var int
     */
    private $requestCounter=0;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $logger;

    public function __construct(
        ?string $passwordHash,
        ?int $memberId,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

        if ($memberId && $passwordHash) {
            $cookieParams = [
                'ipb_member_id' => $memberId,
                'ipb_pass_hash' => $passwordHash,
                "hath_perks" => "m1.m2.m3.tf.t1.t2.t3.p1.p2.s-210aa44613",
            ];

            $cookieJar = new CookieJar();

            $domains = ['.ehentai.org', '.exhentai.org','.e-hentai.org'];
            foreach($domains as $domain) {
                foreach ($cookieParams as $key => $value) {
                    $cookieJar->setCookie(new SetCookie([
                        'Name'   => $key,
                        'Value'  => $value,
                        'Domain' => $domain
                    ]));
                }
            }

            $this->cookieJar = $cookieJar;
        }

        $this->initClient();
    }


    private function initClient()
    {
        $this->history = Middleware::history($this->guzzleContainer);
        $stack = HandlerStack::create();
        $stack->push($this->history);

        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'cookies'  => $this->cookieJar,
            'defaults' => [
                'allow_redirects' => [
                    'max'             => 5,
                    'refer'           => true,
                    'track_redirects' => true
                ],
                'headers' => [
                    'User-Agent' => self::USER_AGENT
                ]
            ],
            'handler' => $stack
        ]);
    }

    public function logout()
    {
        $this->cookieJar->clear();
    }

    public function getTagSearchQuery(string $tag)
    {
        if(strpos($tag,'$') === false) $tag = $tag.'$';
        if(strpos($tag,':') !== false) {
            list($namespace, $tagname) = explode(':', $tag);
            // Tags containing spaces in name are quoted
            if(strpos($tagname, ' ') !== false) {
                $tag = $namespace.':"'.$tagname. '"';
            }
        }

        return str_replace(':','%3A', $tag);
    }

    public function getByTag(string $tag, int $page = null)
    {
        return $this->searchRemote($this->getTagSearchQuery($tag), $page);
    }

    public function search(string $query, int $page = null)
    {
        return $this->searchRemote($query, $page);
    }

    public function searchRemote(string $query, int $page = null)
    {
        if(strpos($query, ':') !== FALSE)
            return $this->getByTag($query, $page);

        return $this->getGalleriesFromOverview($this->get('/', [
            'f_search' => $query,
            'page'   => $page
        ]));
    }

    public function searchLocal(string $query, int $page = null)
    {

    }

    /**
     * @param int|null $page
     * @return ExhentaiGallery[]
     */
    public function getIndex(int $page = null)
    {
        $data = $this->get('/', ['page' => $page]);
        return $this->getGalleriesFromOverview($data);
    }

    private function getGalleriesFromOverview(string $html)
    {
        if(preg_match_all('~https:\/\/e(?:-|x)hentai.org\/g\/([0-9]+)\/([0-9a-f]+)\/~i', $html, $matches, PREG_PATTERN_ORDER)) {
            $tokenList = [];

            for ($i=0; $i < count($matches[0]); $i++) {
                $tokenList[$matches[1][$i]] = new GalleryToken($matches[1][$i], $matches[2][$i]);
            }

            return $this->getGalleries($tokenList);
        }

        throw new \Exception('No galleries found in overview');
    }

    public function getGallery(int $id, string $token): ExhentaiGallery
    {
        return $this->getGalleries([new GalleryToken($id, $token)])[0];
    }

    /**
     * @param GalleryToken[] ...$tokens
     * @return ExhentaiGallery[]
     */
    public function getGalleries(array $tokens)
    {
        $galleries = [];
        // Check if we're trying to lookup more than 25 galleries (API LIMIT)
        // If so, split up and request per 25 galleries
        if(count($tokens) > 25) {
            $apiCalls = ceil(count($tokens)/25);

            for($i=0;$i<=$apiCalls;$i++) {
                $galleryTokens = array_splice($tokens, 0, 25);
                $newGalleries = $this->getGalleries($galleryTokens);
                $galleries = array_merge($galleries, $newGalleries);
            }
        } else {
            $gidList = [];
            /** @var GalleryToken $token */
            foreach($tokens as $token) {
                $gidList[] = [
                    $token->getId(),
                    $token->getToken()
                ];
            }

            $apiGalleries = $this->api('gdata', [
                'gidlist' => $gidList,
                'namespace' => 1
            ]);

            $response = json_decode($apiGalleries->getBody()->getContents());

            if(isset($response->gmetadata)) {
                foreach($response->gmetadata as $metadata) {
                    $galleries[] = $this->entityManager->getRepository(ExhentaiGallery::class)->fromApi($metadata);
                }
            }
        }
        return $galleries;
    }

    public function getGalleryPage(string $token, int $galleryId, int $page = null)
    {

    }

    public function downloadGallery(ExhentaiGallery $gallery, $method = 'zip')
    {
        switch ($method) {
            case 'zip':
                return $this->downloadGalleryZip($gallery);
                break;
            case 'scrape':
                return $this->downloadGalleryScrape($gallery);
                break;
            case 'hath':
                return $this->downloadGalleryHath($gallery);
                break;
            default:
                throw new \Exception('download method not supported');
        }
    }

    public function downloadGalleryZip(ExhentaiGallery $gallery, $resampled = false)
    {
        if($gallery->getArchiverKey()->getTime() < new \DateTime('-24 hours')) {
            // Renew Archiver key
            $newGallery = $this->getGallery($gallery->getId(), $gallery->getToken());
            $gallery->setArchiverKey($newGallery->getArchiverKey());
        }

        $archiverPageHtml = $this->get('archiver.php', [
            'gid'   => $gallery->getId(),
            'token' => $gallery->getToken(),
            'or'    => $gallery->getArchiverKey()->getToken()
        ]);

        $formOffset = ($resampled === true) ? 0 : 1;

        $crawler = new Crawler($archiverPageHtml);
        $formNode = $crawler->filterXPath("//div[1]/div/div[{$formOffset}]/form");

        if($formNode->count()) {
            $downloadQueueHtml = $this->request('POST', $formNode->filter('form')->attr('action'), [
                'form_params' => [
                    'dltype'  => $formNode->filterXPath('//input[@name="dltype"]')->attr('value'),
                    'dlcheck' => $formNode->filterXPath('//div/input[@name="dlcheck"]')->attr('value')
                ]
            ])->getBody()->getContents();

            $crawler = new Crawler($downloadQueueHtml);
            if(strpos($crawler->text(), "Locating archive server") !== FALSE) {
                // Gallery is being archived
                $attempt = 0;
                $downloadSucces = false;
                while (!$downloadSucces) {
                    // This can take a while depending on gallery size and popularity
                    $url = null;
                    $crawler = new Crawler(
                        $this->get(
                            $crawler->filterXPath('//p[@id="continue"]/a')->attr('href'),
                            [
                                'on_stats' => function (TransferStats $stats) use (&$url) {
                                    $url = $stats->getEffectiveUri();
                                }
                            ]
                        )
                    );

                    $downloadUri = sprintf("http://%s%s", current($this->lastResponse->getHeader('host')), $crawler->filterXPath('//a')->attr('href'));
                    if(strpos($downloadUri, 'start=1') !== FALSE) {
                        $this->request('GET', $downloadUri, ['save_to' => '']);

                        if(substr($this->lastResponse->getStatusCode(),0,1) == 2) {
                            $downloadSucces = true;
                        }
                    }
                    // Sleep if we have to rety (it says so in the embedded javascript ;))
                    sleep(1);
                    $attempt++;
                    if($attempt >= 20) {
                        throw Exception('Unable to download archive as zip. Failed after 20 attempts');
                    }
                }
            }
        } else {
            // @todo add support for galleries already downloaded
            throw new \Exception('Download form not found');
        }
    }

    public function downloadGalleryScrape(ExhentaiGallery $gallery)
    {

    }

    public function downloadGalleryHath(ExhentaiGallery $gallery)
    {

    }

    public function get(string $uri, array $parameters = [])
    {
        if(count($parameters))
            $uri = sprintf('%s?%s', $uri, urldecode(http_build_query($parameters)));

        $this->logger->debug('Sending GET request', [
            'uri'        => $uri,
            'parameters' => $parameters,
            'cookie'     => $this->cookieJar->toArray()
        ]);

        $response = $this->request('GET', $uri);

        $this->logger->debug('RESPONSE', [
            'code' => $response->getStatusCode()
        ]);

        $responseBody = $response->getBody()->getContents();

        return $responseBody;
    }

    private function api(string $method, array $payload): ResponseInterface
    {
        return $this->request('POST', sprintf(self::API_URL), [
            RequestOptions::JSON => array_merge([
                'method' => $method
            ], $payload)
        ]);
    }

    public function request(string $method, $uri = '/', $parameters = [])
    {
        $this->requestCounter++;
        if(!$this->lastRequest)
            $this->lastRequest = new \DateTime();

        if ($this->requestCounter > 4 && $this->rateLimiterEnabled) {
            sleep(7);
            $this->requestCounter = 0;
        }

        $this->lastResponse = $this->client->request($method, $uri, $parameters);

        return $this->lastResponse;
    }


    public function getHistory()
    {
        return $this->guzzleContainer;
    }

    /**
     * @return Client
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    /**
     * @return CookieJar
     */
    public function getCookieJar(): CookieJar
    {
        return $this->cookieJar;
    }


}
