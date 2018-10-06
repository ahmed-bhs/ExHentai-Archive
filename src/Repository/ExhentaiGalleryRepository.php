<?php

namespace App\Repository;

use App\Entity\ExhentaiArchiverKey;
use App\Entity\ExhentaiCategory;
use App\Entity\ExhentaiGallery;
use App\Entity\ExhentaiTag;
use App\Entity\ExhentaiTagNamespace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExhentaiGallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExhentaiGallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExhentaiGallery[]    findAll()
 * @method ExhentaiGallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExhentaiGalleryRepository extends ExHentaiRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExhentaiGallery::class);
    }

    public function fromApi(\stdClass $json, $downloadstate = 0)
    {
        $gallery = $this->find($json->gid);

        if(!$gallery) {
            $gallery = new ExhentaiGallery();
            $gallery
                ->setArchiverKey(
                    $this->_em->getRepository(ExhentaiArchiverKey::class)->findOneOrCreate(
                        ['token' => $json->archiver_key, 'Gallery' => $gallery], (new ExhentaiArchiverKey($json->archiver_key))->setGallery($gallery)))
                ->setCategory(
                    $this->_em->getRepository(ExhentaiCategory::class)->findOneOrCreate(
                        ['Title'=> $json->category], (new ExhentaiCategory())->setTitle($json->category)
                    ));
        } else {
            if($gallery->getLastAudit() > new \DateTime("-1 day")) {
                return $gallery;
            }
        }

        $gallery
            ->setId($json->gid)
            ->setToken($json->token)
            ->setTitle($json->title)
            ->setTitleJapan($json->title_jpn)
            ->setUploader($json->uploader)
            ->setPosted(new \DateTime('@'.$json->posted))
            ->setFileCount($json->filecount)
            ->setFilesize($json->filesize)
            ->setExpunged($json->expunged)
            ->setRating($json->rating)
            ->setTorrentCount($json->torrentcount)
            ->setDownloadState($downloadstate)
            ->setLastAudit(new \DateTime());

        $this->_em->getRepository(ExhentaiGallery::class)->findOneOrCreate([
            'id' => $gallery->getId()
        ], $gallery);

        foreach($json->tags as $tag) {
            $tagObj = new ExhentaiTag();

            $tagCriteria = [];

            if(strpos($tag, ':') !== FALSE) {
                list($namespace, $tagString) = explode(':', $tag);

                $tagObj->setNamespace($this->_em->getRepository(ExhentaiTagNamespace::class)
                        ->findOneOrCreate(['Name' => $namespace], (new ExhentaiTagNamespace())->setName($namespace))
                )->setName($tagString);

                $tagCriteria['Namespace'] = $tagObj->getNamespace()->getId();
            } else {
                $tagObj->setName($tag);
            }
            $tagObj->addGallery($gallery);

            $tagCriteria['Name'] = $tagObj->getName();

            $tagObj = $this->_em->getRepository(ExhentaiTag::class)->findOneOrCreate($tagCriteria, $tagObj);

            $gallery->addTag($tagObj);
        }

        $this->_em->flush();

        return $gallery;
    }

    public function findAllPaginated($page = 1, $limit = 20)
    {
        return $this->paginate($this->createQueryBuilder('e')->orderBy('e.Posted'), $page, $limit);
    }

    public function paginate(QueryBuilder $builder, $page = 1, $limit = 20)
    {
        $paginator = new Paginator($builder);
        $paginator->setUseOutputWalkers(false);

        $paginator->getQuery()
            ->setFirstResult($limit*($page-1))
            ->setMaxResults($limit);
    }

//    /**
//     * @return ExhentaiGallery[] Returns an array of ExhentaiGallery objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ExhentaiGallery
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
