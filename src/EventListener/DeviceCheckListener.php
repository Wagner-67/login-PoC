<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;

final class DeviceCheckListener
{
    #[AsEventListener(event: 'kernel.request')]
    public function onRequestEvent(RequestEvent $event): void
    {
            $request = $event->getRequest();

            if($request->attributes->get('/api/') !== 'api_login_check') {
                return;
            }
    }
}
