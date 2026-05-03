<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        $userSubject = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($userSubject, $user),
            self::EDIT => $this->canEdit($userSubject, $user, $vote),
            default => throw new LogicException('This code should not be reached.')
        };
    }

    private function canView(User $userSubject, User $user): bool
    {
        if ($this->canEdit($userSubject, $user, null)) {
            return true;
        }

        return false;
    }

    private function canEdit(User $userSubject, User $user, ?Vote $vote): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($userSubject === $user) {
            return true;
        }

        $vote?->addReason(sprintf(
            'The logged in user (username: %s) is not this user or Admin (id: %d).',
            $user->getFirstName(), $userSubject->getId(),
        ));

        return false;
    }
}
