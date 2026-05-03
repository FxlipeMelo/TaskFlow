<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        $task = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($task, $user),
            self::EDIT => $this->canEdit($task, $user, $vote),
            default => throw new \LogicException('This code should not be reached.')
        };
    }

    private function canView(Task $task, User $user): bool
    {
        if ($this->canEdit($task, $user, null)) {
            return true;
        }

        return !$task->isPrivate();
    }

    private function canEdit(Task $task, User $user, ?Vote $vote): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($user === $task->getUser()) {
            return true;
        }

        $vote?->addReason(sprintf(
            'The logged in user (username: %s) is not the author of this post (id: %d).',
            $user->getFirstName(), $task->getId()
        ));

        return false;
    }
}
