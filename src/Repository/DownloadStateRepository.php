<?php

namespace App\Repository;

use App\Entity\DownloadState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DownloadState|null find($id, $lockMode = null, $lockVersion = null)
 * @method DownloadState|null findOneBy(array $criteria, array $orderBy = null)
 * @method DownloadState[]    findAll()
 * @method DownloadState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DownloadStateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DownloadState::class);
    }

//    /**
//     * @return DownloadState[] Returns an array of DownloadState objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DownloadState
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
