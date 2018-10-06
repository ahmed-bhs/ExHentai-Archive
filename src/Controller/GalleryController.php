<?php

namespace App\Controller;

use App\Entity\ExhentaiGallery;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Match;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class GalleryController extends Controller
{
    /**
     * @Route("/gallery/index/{page}", defaults={"page": 1}, name="galleryindex")
     */
    public function index($page)
    {
        $finder = $this->container->get('fos_elastica.finder.app.gallery');

        $query = new Query();
        $query->addSort(['posted' => ['order' => 'desc']]);

        $paginator = $finder->findPaginated($query);
        $paginator->setMaxPerPage(10);
        $paginator->setCurrentPage($page);

        return $this->json($paginator->getCurrentPageResults());
    }

    /**
     * @Route("/gallery/search/{query}/{page}", defaults={"page": 1}, name="search")
     */
    public function search($query, $page)
    {
        $finder = $this->container->get('fos_elastica.finder.app.gallery');

        $boolQuery = new BoolQuery();
        $titleQuery = new Match('title', $query);
        $titleJPQuery = new Match('titleJapan', $query);
        $boolQuery->addShould($titleQuery);
        $boolQuery->addShould($titleJPQuery);

        $paginator = $finder->findPaginated($boolQuery);
        $paginator->setMaxPerPage(10);
        $paginator->setCurrentPage($page);

        return $this->json($paginator->getCurrentPageResults());
    }


    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @final
     */
    protected function json($data, int $status = 200, array $headers = array(), array $context = array()): JsonResponse
    {
        return new JsonResponse($this->container->get('jms_serializer')->serialize($data, 'json'), $status, $headers, true);
    }
}
