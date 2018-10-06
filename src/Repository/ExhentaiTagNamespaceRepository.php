<?php

namespace App\Repository;

use App\Entity\ExhentaiTagNamespace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExhentaiTagNamespace|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExhentaiTagNamespace|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExhentaiTagNamespace[]    findAll()
 * @method ExhentaiTagNamespace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExhentaiTagNamespaceRepository extends ExHentaiRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExhentaiTagNamespace::class);
    }

//    /**
//     * @return ExhentaiTagNamespace[] Returns an array of ExhentaiTagNamespace objects
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
    public function findOneBySomeField($value): ?ExhentaiTagNamespace
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
