<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class AccountVerifiedListener
{
    private array $whitelistedRoutes;
    private Security $security;

    public function __construct(Security $security, array $whitelistedRoutes = [])
    {
        $this->security = $security;
        $this->whitelistedRoutes = $whitelistedRoutes;
    }

public function onKernelController(ControllerEvent $event)
{
    $request = $event->getRequest();
    $route = $request->attributes->get('_route');

    if (in_array($route, $this->whitelistedRoutes)) {
        return;
    }

    $user = $this->security->getUser();

    if (!$user) {
        return;
    }

    if (!$user->isVerified()) {
        $event->setController(fn() => new JsonResponse(['error' => 'Account nicht verifiziert'], 403));
        return;
    }
}

}
