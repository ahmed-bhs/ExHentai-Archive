<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $Logger)
    {
        $this->logger = $Logger;
    }

    public function index()
    {
        return new Response(file_get_contents(__DIR__.'/../../public/index.html'));
    }

    public function fallback()
    {
        \Log::$monolog = $this->logger;
        require __DIR__ . '/../../legacy/www/api.php';
    }
}
