<?php
namespace App\Service;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\DomCrawler\Crawler;

class ExHentaiBrowserService
{
    const BASE_URL       = 'https://exhentai.org/';
    const LOGIN_BASE_URL = 'https://e-hentai.org/';
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

    public function getGallery(int $id, string $token)
    {

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
}
