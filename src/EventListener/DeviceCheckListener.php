<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

final class DeviceCheckListener

{
    private Security $security;
    private EntityManagerInterface $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    #[AsEventListener(event: 'kernel.request')]
    public function onRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if ($route !== 'api_login_check') {
            return;
        }

        $user = $this->security->getUser();
        if (!$user || !$user->isMfaEnabled()) {
            return;
        }

        $userId = $user->getUserId();

        $existingMfa = $this->em->getRepository(Mfa::class)->findOneBy([
            'userId' => $userId,
        ]);

        if(!$existingMfa) {
            return;
        }
    }
}