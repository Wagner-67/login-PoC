<?php

namespace App\Controller;

use App\Entity\TwoFactorAuth;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class TwoFactorController extends AbstractController
{
    #[Route('/auth/TwoFactorAuthE', name: 'auth_TwoFactorAuth', methods: ['GET'])]
    public function enable2FA(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $userid = $user->getUserid();

        if (!$user->isTwoFactorEnabled()) {
            $user->setTwoFactorEnabled(true);

            $now = new \DateTime();

            $twoFactor = new TwoFactorAuth();
            $twoFactor->setUserId($userid);
            $twoFactor->setLastLogin($now);
            $twoFactor->setLast2fa($now);
            $twoFactor->setLoginCount(1);

            $em->persist($user);
            $em->persist($twoFactor);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'twoFactorEnabled' => $user->isTwoFactorEnabled(),
                'lastLogin' => $now->format('Y-m-d H:i:s'),
                'loginCount' => $twoFactor->getLoginCount(),
            ]);
        }

        return new JsonResponse(['message' => '2FA bereits aktiviert'], 200);
    }

    #[Route('/auth/TwoFactorAuthD', name: 'auth_TwoFactorAuthD', methods: ['POST'])]
    public function disable2FA(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $password = $data['password'] ?? null;

        if (empty($password)) {
            return new JsonResponse(['error' => 'Passwort muss angegeben werden'], 400);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Falsches Passwort'], 403);
        }

        if (!$user->isTwoFactorEnabled()) {
            return new JsonResponse(['message' => '2FA ist bereits deaktiviert'], 200);
        }

        $user->setTwoFactorEnabled(false);

        $twoFactor = $em->getRepository(TwoFactorAuth::class)
            ->findOneBy(['userId' => $user->getUserid()]);

        if ($twoFactor) {
            $em->remove($twoFactor);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => '2FA wurde erfolgreich deaktiviert',
        ]);
    }
}
