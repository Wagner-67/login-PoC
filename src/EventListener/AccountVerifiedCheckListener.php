<?php

namespace App\EventListener;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountVerifiedListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'onCheckPassport',
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        $user = $event->getPassport()->getUser();

        if ($user instanceof UserInterface && method_exists($user, 'isVerified')) {
            if (!$user->isVerified()) {
                throw new CustomUserMessageAuthenticationException(
                    'Account nicht verifiziert'
                );
            }
        }
    }
}
