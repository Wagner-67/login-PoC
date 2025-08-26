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

    #[Route('/public/TwoFactorAuthCode', name: 'public_TwoFactor_AuthCode', methods: ['POST'])]
    public function checkTwoFactorToken(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userid'], $data['code'])) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        $twoFactorAuth = $em->getRepository(TwoFactorAuth::class)->findOneBy([
            'userid' => $data['userid']
        ]);

        if (!$twoFactorAuth) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        if ($twoFactorAuth->getTwoFactorAuthToken() !== $data['code']) {
            return new JsonResponse(['error' => 'Invalid code'], 401);
        }

        // ✅ Code korrekt → 2FA erfolgreich
        $twoFactorAuth->setHasToVerify(false);
        $twoFactorAuth->setTwoFactorAuthToken(null); // Code ungültig machen
        $twoFactorAuth->setLast2fa(new \DateTime());
        $twoFactorAuth->setLoginCount(0);

        $em->persist($twoFactorAuth);
        $em->flush();

        // Hier müsstest du jetzt den JWT-Token ausstellen,
        // z.B. über LexikJWTAuthenticationBundle TokenManager
        // (falls du den nicht schon im Login-Prozess zwischengespeichert hast)
        return new JsonResponse([
            'success' => true,
            'message' => 'Two-factor authentication successful',
            // 'token' => $jwtManager->create($twoFactorAuth->getUser())
        ]);
    }

}
