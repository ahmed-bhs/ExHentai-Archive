<?php

namespace App\Repository;

use App\Entity\ExhentaiGallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExhentaiGallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExhentaiGallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExhentaiGallery[]    findAll()
 * @method ExhentaiGallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExhentaiGalleryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExhentaiGallery::class);
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
