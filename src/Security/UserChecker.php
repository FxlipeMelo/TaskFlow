<?php

namespace App\Security;

use App\Domain\Entity\User;
use App\Domain\Enum\UserStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User){
            return;
        }

        if (!$user->isVerified()){
            throw new CustomUserMessageAccountStatusException('Your email address is not verified. Please check your inbox.');
        }

        if ($user->getStatus() === UserStatus::INACTIVE) {
            throw new CustomUserMessageAccountStatusException('Your account has been deactivated. Please contact the administrator.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
