<?php

namespace App\EventListener;

use App\Entity\TwoFactorAuth;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class TwoFactorAuthListener
{
    public function __construct(private EntityManagerInterface $em) {}

    #[AsEventListener(event: 'lexik_jwt_authentication.on_authentication_success')]
    public function onLexikJwtAuthenticationOnAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if(!$user || !$user->isTwoFactorEnabled()) {
            return;
        }

        $userid = $user->getUserid();

        $twoFactorAuth = $this->em->getRepository(TwoFactorAuth::class)->findOneBy(['userid' => $userid]);

        if($twoFactorAuth->hasToVerify()) {
            #Email setup und so
            return;
        }

        if(!$twoFactorAuth) {
            return;
        }

        $lastLogin  = $twoFactorAuth->getLastLogin();
        $loginCount = $twoFactorAuth->getLoginCount();
        $last2fa    = $twoFactorAuth->getLast2fa();

        if($loginCount >= 25) {
            $twoFactorAuth->setHasToVerify(true);
            $twoFactorAuth->setLoginCount(0);
            $this->em->persist($twoFactorAuth);
            $this->em->flush();
        }

                if ($lastLogin && $last2fa) {
            $diff = $lastLogin->diff($last2fa);

            if ($diff->days >= 25) {
                $twoFactorAuth->setHasToVerifie(true);
                $this->em->persist($twoFactorAuth);
                $this->em->flush();
            }
    }
}
