<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getTopUsersByLikes(): array
{
    return $this->createQueryBuilder('p')
        ->select('u.nom as username, SUM(p.likeCount) as totalLikes')
        ->join('p.id_utilisateur', 'u') // Utilise la propriété de l'entité
        ->groupBy('u.id') // Group by sur l'ID utilisateur
        ->orderBy('totalLikes', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
}

public function getTopUsersByComments(): array
{
    return $this->createQueryBuilder('p')
        ->select('u.nom as username, COUNT(c) as totalComments') // Utilise l'alias directement
        ->join('p.id_utilisateur', 'u')
        ->leftJoin('p.commentaires', 'c') // Utilise la propriété 'commentaires' de Post
        ->groupBy('u.id')
        ->orderBy('totalComments', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
}
public function findAllTitles(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.titre') 
            ->getQuery()
            ->getSingleColumnResult();
    }
}
