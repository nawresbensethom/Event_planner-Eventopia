<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Recherche et filtration des événements avec pagination.
     *
     * @param string|null $searchTerm Terme de recherche (description)
     * @param string|null $category Catégorie pour filtrer
     * @return array Liste des événements correspondant aux critères
     */
    public function findBySearchAndCategory(?string $searchTerm = null, ?string $category = null): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($searchTerm) {
            $qb->andWhere('e.description LIKE :searchTerm')
               ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if ($category) {
            $qb->andWhere('e.category = :category')
               ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }
}