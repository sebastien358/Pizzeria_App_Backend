<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin/client')]
#[IsGranted("ROLE_ADMIN")]
class ClientAdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserService $userService;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, UserService $userService) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    #[Route('/list', methods: ['GET'])]
    public function index(SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $clients = $this->entityManager->getRepository(User::class)->findAll();

            $dataClients = $this->userService->getDataService($clients, $serializer);

            return $this->json($dataClients, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la récupérartion des utilisateurs', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/search', methods: ['GET'])]
    public function search(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $data = $request->query->get('search');

            $search =  trim((string) $data);

            if (!$search && !is_string($search)) {
                return $this->json(['error' => 'Search string expected'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $clients = $this->entityManager->getRepository(User::class)->findAllSearchClients($search);
            $dataClients = $this->userService->getDataService($clients, $serializer);

            return $this->json($dataClients, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la recherche des utilisateurs', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/is-visible/{id}', methods: ['PATCH'])]
    public function isVisible(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $client = $this->entityManager->getRepository(User::class)->find($id);

            if (!$client) {
                return $this->json(['message' => 'Client introuvable'], Response::HTTP_NOT_FOUND);
            }

            if ($client->getId() === $user->getId()) {
                return $this->json(['message' => 'Vous ne pouvez pas modifier votre propre visibilité'], Response::HTTP_FORBIDDEN);
            }

            $client->setIsVisible(!$client->getIsVisible());

            $this->entityManager->flush();

            return $this->json(['success' => true], Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la visibilité des utilisateurs : ', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/remove/{id}', methods: ['DELETE'])]
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $client = $this->entityManager->getRepository(User::class)->find($id);

            if (!$client) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
            }

            if ($client->getId() === $user->getId()) {
                return $this->json(['message' => 'Vous ne pouvez pas modifier votre propre visibilité'], Response::HTTP_FORBIDDEN);
            }

            $this->entityManager->remove($client);
            $this->entityManager->flush();

            return $this->json(['message' => 'Le client a été supprimé'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la suppression d\'un utilisateur : ', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
