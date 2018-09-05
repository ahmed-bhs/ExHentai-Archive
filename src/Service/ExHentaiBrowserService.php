<?php
namespace App\Service;

use App\Entity\ExhentaiGallery;
use App\Model\GalleryToken;
use Doctrine\Common\Collections\ArrayCollection;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\DomCrawler\Crawler;

class ExHentaiBrowserService
{
    const BASE_URL       = 'https://exhentai.org/';
    const LOGIN_BASE_URL = 'https://e-hentai.org/';
    const API_URL        = 'https://api.e-hentai.org/api.php';
    const USER_AGENT     = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';
    /**
     * @var array
     */
    private $guzzleContainer = [];

    /**
     * @var Middleware
     */
    private $history;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var CookieJar
     */
    private $cookieJar = null;

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
            foreach($cookieParams as $key => $value) {
                $cookieJar->set(new Cookie(
                    $key,
                    $value,
                    null,
                    null,
                    '.exhentai.org'
                ));
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

        $guzzleClient = new GuzzleClient([
            'base_uri' => self::BASE_URL,
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

        $this->client = new Client([], null, $this->cookieJar);
        $this->client->setClient($guzzleClient);
    }

    public function login(string $username, string $password)
    {
        $loginPage = self::LOGIN_BASE_URL.'bounce_login.php';
        $crawler = $this->get($loginPage);
        $form = $crawler->selectButton('Login!')->form();
        $formResult = $this->client->submit($form, [
            'UserName' => $username,
            'password' => $password
        ]);

        // @todo handle login and assign cookie to exhentai domain
    }

    public function logout()
    {

    }

    public function search(string $query, int $page = 0)
    {

    }

    public function getIndex(int $page = 0)
    {

    }

    public function getGallery(int $id, string $token): ExhentaiGallery
    {
        return $this->getGalleries([new GalleryToken($id, $token)])->first();
    }

    /**
     * @param GalleryToken[] ...$tokens
     * @return ArrayCollection|ExhentaiGallery[]
     */
    public function getGalleries(array $tokens)
    {
        $galleries = new ArrayCollection();
        // Check if we're trying to lookup more than 25 galleries (API LIMIT)
        // If so, split up and request per 25 galleries
        if(count($tokens) > 25) {
            while(count($tokens)) {
                $galleryTokens = array_splice($tokens, 0, 25);
                $this->getGalleries($galleryTokens);
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
                    $galleries->add(ExhentaiGallery::fromApi($metadata));
                }
            }

            return $galleries;
        }
    }

    public function getGalleryPage(string $token, int $galleryId, int $page)
    {

    }

    public function downloadGallery()
    {

    }

    private function get(string $uri, array $parameters = []): Crawler
    {
        return $this->client->request('GET', $uri, $parameters);
    }

    private function api(string $method, array $payload): ResponseInterface
    {
        return $this->client->getClient()->post(sprintf(self::API_URL), [
            RequestOptions::JSON => array_merge([
                'method' => $method
            ], $payload)
        ]);
    }
}
