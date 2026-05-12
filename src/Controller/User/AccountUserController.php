<?php

namespace App\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/user')]
#[IsGranted('ROLE_USER')]
final class AccountUserController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/account/delete', methods: ['DELETE'])]
    public function removeAccount(): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return $this->json(['message' => 'Le compte a bien été supprimé'], Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la supression du compte', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Erreur de la supression du compte'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
