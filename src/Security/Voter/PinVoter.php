<?php

namespace App\Security\Voter;

use function PHPUnit\Framework\returnSelf;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PinVoter extends Voter
{
    public const PIN_MANAGE = 'PIN_MANAGE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::PIN_MANAGE])
            && $subject instanceof \App\Entity\Pin;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::PIN_MANAGE:
                return $user->isVerified() && $user == $subject->getUser() || in_array("ROLE_ADMIN", $user->getRoles());
        }

        return false;
    }
}
// $user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"];