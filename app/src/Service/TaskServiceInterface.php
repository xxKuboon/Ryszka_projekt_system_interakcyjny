<?php

/**
 * Task service interface.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface TaskServiceInterface.
 */
interface TaskServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int           $page     Page number
     * @param Category|null $category Category filter
     * @param User|null     $author   Author filter
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page, ?Category $category = null, ?User $author = null): PaginationInterface;

    /**
     * Get paginated upcoming list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListUpcoming(int $page): PaginationInterface;

    /**
     * Get paginated ongoing list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListOngoing(int $page): PaginationInterface;

    /**
     * Get paginated ended list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListEnded(int $page): PaginationInterface;

    /**
     * Get paginated list by author.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListByAuthor(int $page, User $author): PaginationInterface;

    /**
     * Get paginated list by status.
     *
     * @param int        $page   Page number
     * @param TaskStatus $status Status
     *
     * @return PaginationInterface Pagination
     */
    public function getPaginatedListByStatus(int $page, TaskStatus $status): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Task $task Task entity
     */
    public function save(Task $task): void;

    /**
     * Delete entity.
     *
     * @param Task $task Task entity
     */
    public function delete(Task $task): void;
}
