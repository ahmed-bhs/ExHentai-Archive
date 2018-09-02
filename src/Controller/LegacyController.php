<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyController extends AbstractController
{
    public function index()
    {
        return new Response(file_get_contents(__DIR__.'/../../public/index.html'));
    }

    public function fallback(Request $request, $path)
    {
        require __DIR__ . '/../../legacy/www/api.php';
    }
}
