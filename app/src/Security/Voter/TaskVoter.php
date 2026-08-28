<?php

/**
 * Task voter.
 */

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TaskVoter.
 */
final class TaskVoter extends Voter
{
    /**
     * Delete permission.
     *
     * @var string
     */
    public const DELETE = 'TASK_DELETE';

    /**
     * Edit permission.
     *
     * @var string
     */
    public const EDIT = 'TASK_EDIT';

    /**
     * View permission.
     *
     * @var string
     */
    public const VIEW = 'TASK_VIEW';

    /**
     * Getting to category page.
     *
     * @var string
     */
    public const CATEGORY = 'CATEGORY_INDEX';

    /**
     * Constructor.
     *
     * @param Security $security Security helper
     */
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * Determines if this voter supports the attribute and subject.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::CATEGORY) {
            return true;
        }

        return in_array($attribute, [self::DELETE, self::EDIT, self::VIEW], true)
            && $subject instanceof Task;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     * @param Vote|null      $vote      Vote object
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $subject instanceof Task && $this->canEdit($subject, $user),
            self::DELETE => $subject instanceof Task && $this->canDelete($subject, $user),
            self::VIEW => $subject instanceof Task && $this->canView($subject, $user),
            self::CATEGORY => $this->canCategory(),
            default => false,
        };
    }

    /**
     * Checks if user can delete task.
     *
     * @param Task          $task Task entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Task $task, UserInterface $user): bool
    {
        return $task->getAuthor()?->getId() === $user->getId() || $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Checks if user can edit task.
     *
     * @param Task          $task Task entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Task $task, UserInterface $user): bool
    {
        return $task->getAuthor()?->getId() === $user->getId() || $this->security->isGranted('ROLE_ADMIN');
    }

    /**
     * Checks if a user can view a task.
     *
     * @param Task          $task Task entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canView(Task $task, UserInterface $user): bool
    {
        return true;
    }

    /**
     * Checks if a user can view a category tab.
     *
     * @return bool Result
     */
    private function canCategory(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
