<?php

namespace App\Repository;

use App\Entity\ExhentaiArchiverKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ExhentaiArchiverKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExhentaiArchiverKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExhentaiArchiverKey[]    findAll()
 * @method ExhentaiArchiverKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExhentaiArchiverKeyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ExhentaiArchiverKey::class);
    }

//    /**
//     * @return ExhentaiArchiverKey[] Returns an array of ExhentaiArchiverKey objects
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
    public function findOneBySomeField($value): ?ExhentaiArchiverKey
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
