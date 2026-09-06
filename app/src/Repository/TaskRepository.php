<?php

/**
 * Task repository.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Enum\TaskStatus;
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
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            Task::class
        );
    }

    /**
     * Query tasks with optional filters (category, author, status).
     *
     * @param Category|null   $category Category filter
     * @param User|null       $author   Author filter
     * @param TaskStatus|null $status   Status filter
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(?Category $category = null, ?User $author = null, ?TaskStatus $status = TaskStatus::APPROVED): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->select(
                'partial t.{id, createdAt, updatedAt, title, status, votesCount, minVotesRequired, startsAt, endsAt}',
                'partial c.{id, title}',
                'partial u.{id, email}'
            )
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.author', 'u')
            ->orderBy('t.createdAt', 'DESC');

        if (null !== $status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if (null !== $category) {
            $qb->andWhere('t.category = :category')
                ->setParameter('category', $category);
        }

        if (null !== $author) {
            $qb->andWhere('t.author = :author')
                ->setParameter('author', $author);
        }

        return $qb;
    }

    /**
     * Query upcoming tasks (startsAt > now).
     *
     * @return QueryBuilder Query builder
     */
    public function queryUpcoming(): QueryBuilder
    {
        $now = new \DateTimeImmutable();
        $qb = $this->queryAll(null, null, TaskStatus::APPROVED);

        return $qb->andWhere('t.startsAt > :now')
            ->setParameter('now', $now)
            ->orderBy('t.startsAt', 'ASC');
    }

    /**
     * Query ongoing tasks (startsAt <= now and endsAt >= now).
     *
     * @return QueryBuilder Query builder
     */
    public function queryOngoing(): QueryBuilder
    {
        $now = new \DateTimeImmutable();
        $qb = $this->queryAll(null, null, TaskStatus::APPROVED);

        return $qb->andWhere('(t.startsAt IS NULL OR t.startsAt <= :now)')
            ->andWhere('(t.endsAt IS NULL OR t.endsAt >= :now)')
            ->setParameter('now', $now)
            ->orderBy('t.createdAt', 'DESC');
    }

    /**
     * Query ended tasks (endsAt < now).
     *
     * @return QueryBuilder Query builder
     */
    public function queryEnded(): QueryBuilder
    {
        $now = new \DateTimeImmutable();
        $qb = $this->queryAll(null, null, TaskStatus::APPROVED);

        return $qb->andWhere('t.endsAt < :now')
            ->setParameter('now', $now)
            ->orderBy('t.endsAt', 'DESC');
    }

    /**
     * Query tasks by status (e.g. Pending for Admin).
     *
     * @param TaskStatus $status Status
     *
     * @return QueryBuilder Query builder
     */
    public function queryByStatus(TaskStatus $status): QueryBuilder
    {
        return $this->queryAll(null, null, $status);
    }

    /**
     * Query tasks by author (shows all statuses for the author).
     *
     * @param User $user Author
     *
     * @return QueryBuilder Query builder
     */
    public function queryByAuthor(User $user): QueryBuilder
    {
        return $this->queryAll(null, $user, null);
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
        $qb = $this->createQueryBuilder('t');

        return $qb->select($qb->expr()->countDistinct('t.id'))
            ->where('t.category = :category')
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
