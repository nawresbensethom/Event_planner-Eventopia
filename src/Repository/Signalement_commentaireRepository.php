<?php

namespace App\Repository;

use App\Entity\Signalement_commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Signalement_commentaire>
 */
class Signalement_commentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Signalement_commentaire::class);
    }

    // Add custom query methods here
}
