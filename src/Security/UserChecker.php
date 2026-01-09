<?php

namespace App\Security;

use App\Enum\UserStatus;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof \App\Entity\User) {
            return;
        }

        if ($user->getStatus() !== UserStatus::ACTIVE) {
            $message = match($user->getStatus()) {
                UserStatus::DISABLED => 'Your account has been disabled. Contact admin.',
                UserStatus::ARCHIVED => 'Your account has been archived. Contact admin.',
                default => 'Your account is not active.',
            };

            throw new CustomUserMessageAccountStatusException($message);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // No post-auth checks needed
    }
}