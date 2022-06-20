<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function add(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param SearchData $search
     * @return Article[]
     */
    public function findSearch(SearchData $search) : array
    {
        $query= $this
            ->createQueryBuilder('art')
            ->join('art.categories', 'cat');

        if (!empty($search->title)) {
            $query = $query
                ->andWhere('art.title LIKE :title')
                ->setParameter('title', "%{$search->title}%");
        }

        if(!empty($search->categories)){
            $query = $query
            ->andWhere('cat.id IN (:categories)')
            ->setParameter('categories', $search->categories);
        }

        return $query->getQuery()->getResult();
    }
}
