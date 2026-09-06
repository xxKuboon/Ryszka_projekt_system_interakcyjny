<?php

/**
 * Task Service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TaskService.
 */
class TaskService implements TaskServiceInterface
{
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param TaskRepository     $taskRepository Task repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(private readonly TaskRepository $taskRepository, private readonly PaginatorInterface $paginator)
    {
    }

    /**
     * Get paginated list of tasks with optional category and author filters.
     *
     * @param int           $page     Page number
     * @param Category|null $category Category filter
     * @param User|null     $author   Author filter
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?Category $category = null, ?User $author = null): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryAll($category, $author, TaskStatus::APPROVED),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated upcoming list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListUpcoming(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryUpcoming(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated ongoing list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListOngoing(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryOngoing(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated ended list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListEnded(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryEnded(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated list of tasks by status.
     *
     * @param int        $page   Page number
     * @param TaskStatus $status Task status
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListByStatus(int $page, TaskStatus $status): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryByStatus($status),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Get paginated list of tasks by author.
     *
     * @param int  $page   Page number
     * @param User $author Author user
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListByAuthor(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryByAuthor($author),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        $this->taskRepository->save($task);
    }

    /**
     * Delete entity.
     *
     * @param Task $task Task entity
     */
    public function delete(Task $task): void
    {
        $this->taskRepository->delete($task);
    }
}
