<?php

/**
 * Task repository.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class TaskRepository.
 *
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }



    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('task')
            ->select(
                'partial task.{id, createdAt, updatedAt, title}',
                'partial category.{id, title}',
                'partial author.{id, email}'
            )
            ->join('task.category', 'category')
            ->leftJoin('task.author', 'author');
    }

    /**
     * Query all records by author.
     *
     * @param User $author User entity
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(User $author): QueryBuilder
    {
        return $this->queryAll()
            ->andWhere('task.author = :author')
            ->setParameter('author', $author);
    }

    /**
     * Count tasks by category.
     *
     * @param Category $category Category
     *
     * @return int Number of tasks in category
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->createQueryBuilder('task');

        return $qb->select($qb->expr()->countDistinct('task.id'))
            ->where('task.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Task $task Task entity
     */
    public function delete(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
