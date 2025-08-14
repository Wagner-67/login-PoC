<?php

namespace App\Controller;

use App\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Mime\Email;

final class UserController extends AbstractController
{
    #[Route('/account/register_new_user', name: 'account_register_new_user', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            return new JsonResponse(['error' => 'Alle Felder Müssen Ausgefüllt werden'], 400);
        }

        if ($data['password'] !== $data['password_confirmation']) {
            return new JsonResponse(['error' => 'Passwort und Bestätigung passen nicht überein'], 400);
        }

        $user = $em->getRepository(UserEntity::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {

            $user = new UserEntity();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);

            $token = Uuid::v4()->toRfc4122();
            $user->setVerificationToken($token);

            $em->persist($user);
            $em->flush();

            $verificationUrl = $this->generateUrl(
                'verify_account',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('p73583347@gmail.com')
                ->to($data['email'])
                ->subject('Account Verifizierung')
                ->html("<p>Bitte bestätige deine Account-Verifizierung mit folgendem Link:</p>
                       <p><a href=\"$verificationUrl\">Account verifizieren</a></p>
                       <p>Solltest du dich nicht registriert haben, ignoriere bitte diese Nachricht.</p>");

            $mailer->send($email);

            return new JsonResponse(['message' => 'Benutzer Registriert']);
        }

        return new JsonResponse(['error' => 'Ein Benutzer mit dieser Email existiert bereits'], 400);
    }

    #[Route('/verify-account/{token}', name: 'verify_account', methods: ['GET'])]
    public function verify(string $token, Request $request, EntityManagerInterface $em
    ): JsonResponse {

        $user = $em->getRepository(UserEntity::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Ungültiger Verifizierungslink.');
        }

        $user->setVerified(true);
        $user->setVerificationToken(null);
        $em->flush();

        return new JsonResponse(['message'=>'Dein Account wurde erfolgreich verifiziert!']);
    }
    #[Route('/api/greetings', methods: ['GET'])]
    public function test(): JsonResponse {
        return new JsonResponse(['message'=>'Hello World']);
    }
}