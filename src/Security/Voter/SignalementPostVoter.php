<?php

namespace App\Security\Voter;

use App\Entity\SignalementPost;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class SignalementPostVoter extends Voter
{
    public function __construct(
        private Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['VIEW'])
            && $subject instanceof SignalementPost;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var SignalementPost $signalementPost */
        $signalementPost = $subject;

        return match($attribute) {
            'VIEW' => $this->canView($signalementPost, $user),
            default => false,
        };
    }

    private function canView(SignalementPost $signalementPost, UserInterface $user): bool
    {
        // Les admins peuvent tout voir
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Les utilisateurs ne peuvent voir que leurs propres signalements
        return $user === $signalementPost->getIdUtilisateur();
    }
} 