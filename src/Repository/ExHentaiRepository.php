<?php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class ExHentaiRepository extends ServiceEntityRepository
{
    public function findOneOrCreate(array $criteria, object $entity)
    {
        $existingEntity = $this->findOneBy($criteria);

        if(null === $existingEntity)
        {
            $this->_em->persist($entity);
            $existingEntity = $entity;
        }

        return $existingEntity;
    }
}
