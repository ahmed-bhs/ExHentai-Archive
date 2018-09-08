<?php

namespace App\Repository;

use App\Entity\ExhentaiGalleryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExhentaiGalleryImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExhentaiGalleryImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExhentaiGalleryImage[]    findAll()
 * @method ExhentaiGalleryImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExhentaiGalleryImageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExhentaiGalleryImage::class);
    }

//    /**
//     * @return ExhentaiGalleryImage[] Returns an array of ExhentaiGalleryImage objects
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
    public function findOneBySomeField($value): ?ExhentaiGalleryImage
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
