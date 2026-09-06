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
    public const DELETE = 'TASK_DELETE';
    public const EDIT = 'TASK_EDIT';
    public const VIEW = 'TASK_VIEW';
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
     * Determines if the attribute and subject are supported.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::CATEGORY === $attribute) {
            return true;
        }

        return in_array($attribute, [self::DELETE, self::EDIT, self::VIEW], true)
            && $subject instanceof Task;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string         $attribute Attribute
     * @param mixed          $subject   Subject
     * @param TokenInterface $token     Token
     * @param Vote|null      $vote      Vote
     *
     * @return bool Result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (self::VIEW === $attribute) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Administrator ma zawsze pełne uprawnienia do wszystkiego
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return match ($attribute) {
            self::EDIT => $subject instanceof Task && $this->canEdit($subject, $user),
            self::DELETE => $subject instanceof Task && $this->canDelete($subject, $user),
            self::CATEGORY => $this->canCategory($user),
            default => false,
        };
    }

    /**
     * Checks if user can delete task.
     *
     * @param Task          $task Task
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Task $task, UserInterface $user): bool
    {
        return $task->getAuthor() === $user || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if user can edit task.
     *
     * @param Task          $task Task
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Task $task, UserInterface $user): bool
    {
        return $task->getAuthor() === $user || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Checks if user can access category index.
     *
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canCategory(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
