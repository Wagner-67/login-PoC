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
        $now  = new \DateTime();

        if (!$user || !$user->isTwoFactorEnabled()) {
            return;
        }

        $userid = $user->getUserid();

        $twoFactorAuth = $this->em->getRepository(TwoFactorAuth::class)->findOneBy(['userid' => $userid]);

        if (!$twoFactorAuth) {
            return;
        }

        if ($twoFactorAuth->hasToVerify()) {
            if (!$twoFactorAuth->getTwoFactorAuthToken()) {
                $code = str_pad((string)random_int(0, 999), 3, '0', STR_PAD_LEFT);
                $twoFactorAuth->setTwoFactorAuthToken($code);

                $this->em->persist($twoFactorAuth);
                $this->em->flush();
            }

            return;
        }

        $lastLogin  = $twoFactorAuth->getLastLogin();
        $loginCount = $twoFactorAuth->getLoginCount();
        $last2fa    = $twoFactorAuth->getLast2fa();

        if ($loginCount >= 25) {
            $twoFactorAuth->setHasToVerify(true);
            $twoFactorAuth->setLoginCount(0);
            $twoFactorAuth->setLast2fa($now);

            $this->em->persist($twoFactorAuth);
            $this->em->flush();
            return;
        }

        if ($lastLogin instanceof \DateTime && $last2fa instanceof \DateTime) {
            if ($lastLogin > $last2fa) {
                $diff = $lastLogin->diff($last2fa);

                if ($diff->days >= 25) {
                    $twoFactorAuth->setHasToVerify(true);
                    $twoFactorAuth->setLast2fa($now);

                    $this->em->persist($twoFactorAuth);
                    $this->em->flush();
                }
            }
        }
        
        $twoFactorAuth->setLoginCount($loginCount + 1);
        $twoFactorAuth->setLastLogin($now);

        $this->em->persist($twoFactorAuth);
        $this->em->flush();
    }
}
