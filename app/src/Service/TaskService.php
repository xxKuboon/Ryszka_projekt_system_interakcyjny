<?php

/**
 * Task service.
 */

namespace App\Service;

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
    /**
     * Items per page.
     *
     * @constant int
     */
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
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => [
                    'task.id',
                    'task.createdAt',
                    'task.updatedAt',
                    'task.title',
                    'category.title',
                ],
                'defaultSortFieldName' => 'task.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get paginated list by author.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListByAuthor(int $page, User $author): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->taskRepository->queryByAuthor($author),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => [
                    'task.id',
                    'task.createdAt',
                    'task.updatedAt',
                    'task.title',
                    'category.title',
                ],
                'defaultSortFieldName' => 'task.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void
    {
        if (null === $task->getId()) {
            $task->setCreatedAt(new \DateTimeImmutable());
        }

        $task->setUpdatedAt(new \DateTimeImmutable());

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
