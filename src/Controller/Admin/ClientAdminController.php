<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin/client')]
#[IsGranted("ROLE_ADMIN")]
class ClientAdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
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

            $dataClients = $serializer->normalize($clients, 'json', ['groups' => ['users'],
                'circular_reference_handlers' => function ($object) {
                    return $object->getId();
                 }
            ]);

            return $this->json($dataClients, Response::HTTP_OK);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
