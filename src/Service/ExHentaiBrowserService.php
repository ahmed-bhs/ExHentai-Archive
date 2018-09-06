<?php
namespace App\Service;

use App\Entity\ExhentaiGallery;
use App\Model\GalleryToken;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Cookie\CookieJar;

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
     * @var int
     */
    private $requestCounter=0;

    public function __construct(
        ?string $username,
        ?string $password,
        ?string $passwordHash,
        ?string $memberId
    )
    {
        if($username && $password) {
            throw new \Exception('Feature not yet implemented');
            $this->login($username, $password);
        } elseif ($memberId && $passwordHash) {
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

//            var_dump($cookieJar);die();

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

    public function login(string $username, string $password)
    {
//        $loginPage = self::LOGIN_BASE_URL.'bounce_login.php';
//        $crawler = $this->get($loginPage);
//        $form = $crawler->selectButton('Login!')->form();
//        $formResult = $this->client->submit($form, [
//            'UserName' => $username,
//            'password' => $password
//        ]);

        // @todo handle login and assign cookie to exhentai domain
    }

    public function logout()
    {

    }

    public function getByTag(string $tag, int $page = null)
    {
        return $this->getGalleriesFromOverview(
            $this->get('/tag/'. $tag, ['page' => $page])
        );
    }

    public function search(string $query, int $page = null)
    {
        if(strpos($query, ':') !== FALSE)
            return $this->getByTag($query, $page);

        return $this->getGalleriesFromOverview($this->get('/', [
            'search' => $query,
            'page'   => $page
        ]));
    }

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
        } else {
            var_dump('NOT A MATCH');die();
        }
    }

    public function getGallery(int $id, string $token): ExhentaiGallery
    {
        return $this->getGalleries([new GalleryToken($id, $token)])->first();
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
                    $galleries[] = ExhentaiGallery::fromApi($metadata);
                }
            }
        }
        return $galleries;
    }

    public function getGalleryPage(string $token, int $galleryId, int $page = null)
    {

    }

    public function downloadGallery()
    {

    }

    private function get(string $uri, array $parameters = [])
    {
        if($parameters)
            $uri = sprintf('%s?%s', $uri, http_build_query($parameters));

        $response = $this->request('GET', $uri);

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
        if(!$this->lastRequest)
            $this->lastRequest = new \DateTime();

        if(!$this->requestCounter >= 4) {
            $this->requestCounter++;
        } else {
            // Rate limit reached
            if($this->rateLimiterEnabled)
                sleep(5);
            $this->requestCounter=0;
        }

        return $this->client->request($method, $uri, $parameters);
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
}
