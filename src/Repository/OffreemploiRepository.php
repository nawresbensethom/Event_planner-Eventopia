<?php

namespace App\Repository;

use App\Entity\Offreemploi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offreemploi>
 */
class OffreemploiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offreemploi::class);
    }

    // Add custom query methods here
}
