<?php

namespace App\EventListener;

use App\Entity\Mfa;
use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

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

        $userEmail = $user->getEmail();
        $userId = $user->getUserId();

        $existingMfa = $this->em->getRepository(Mfa::class)->findOneBy([
            'userId' => $userId,
        ]);

        $fingerprintCheck = $request->getClientIp() . $request->headers->get('User-Agent');
        $fingerprint = $this->em->getRepository(Mfa::class)->findOneBy([
            'fingerPrint' => $fingerprint,
        ]);

        $userSuspicious = $this->em->getRepository(UserEntity::class)->find($userId);

        if ($fingerprint !== $fingerprintCheck) {
            $userSuspicious->setSuspicious(true);

            $this->em->persist($userSuspicious);
            $this->em->flush();
        }

        if ($userSuspicious->isSuspicious()) {
            
            $email = (new Email())
                ->from('p73583347@gmail.com')
                ->to($userEmail)
                ->subject('Sicherheitswarnung')
                ->html("
                    <p>Hallo,</p>
                    <p>Ein unbekanntes Gerät hat versucht, sich in deinen Account einzuloggen.</p>
                    <p>Keine Sorge: Der Anmeldeversuch wurde abgefangen und blockiert.</p>
                    <p>Nutze den Button unten, um das Gerät freizugeben und für zukünftige Anmeldungen zu speichern:</p>
                    <p><a href='#' style='display:inline-block; padding:10px 20px; background-color:#007bff; color:#fff; text-decoration:none; border-radius:5px;'>Gerät freigeben</a></p>
                    <p>Wenn dieser Versuch nicht von dir stammt, ignoriere diese Nachricht und ändere sofort dein Passwort!</p>
                ");

            $this->mailer->send($email);
        }
    }
}
