<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class ContactAdminController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/contact/list', methods: ['GET'])]
    public function index(SerializerInterface $serializer): JsonResponse
    {
        try {
            $contacts = $this->entityManager->getRepository(Contact::class)->findAll();

            $dataContacts = $serializer->normalize($contacts, 'json', ['groups' => ['contacts'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json($dataContacts, Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Impossible de récupérer la liste des messages', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Impossible de récupérer la liste des messages'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contact/remove/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
//            $user = $this->getUser();
//
//            if (!$user) {
//                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
//            }

            $contact = $this->entityManager->getRepository(Contact::class)->find($id);

            if (!$contact) {
                return $this->json(['error' => "Le message n'existe pas"], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($contact);
            $this->entityManager->flush();

            return $this->json(['message' => 'Le message a été supprimé'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Impossible de supprimer le message', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Impossible de supprimer le message'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
