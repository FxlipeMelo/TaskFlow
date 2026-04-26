<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserStatus;
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
