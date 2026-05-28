<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $limit = (int) $request->query->get('limit');
            $currentPage = (int) $request->query->get('currentPage');

            if (!is_numeric($limit) || !is_numeric($currentPage)) {
                return $this->json(['error' => 'Les données ne correspondent pas aux valeurs attendu'], Response::HTTP_BAD_REQUEST);
            }

            $contacts = $this->entityManager->getRepository(Contact::class)->findAllContactsPagination($currentPage, $limit);

            $dataContacts = $serializer->normalize($contacts, 'json', ['groups' => ['contacts'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            $totalContacts = $this->entityManager->getRepository(Contact::class)->countContacts();
            $countContactsUnread =  $this->entityManager->getRepository(Contact::class)->countContactsUnread();

            return $this->json([
                'total' => (int) $totalContacts,
                'contacts' => $dataContacts,
                'totalContacts' => (int) $totalContacts,
                'countContactsUnread' => (int) $countContactsUnread,
                'pages' => ceil($totalContacts / $limit),
            ], Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Impossible de récupérer la liste des messages', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Impossible de récupérer la liste des messages'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contact/is-read/{id}', methods: ['PATCH'])]
    public function isRead(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $contact = $this->entityManager->getRepository(Contact::class)->find($id);
            if (!$contact) {
                return $this->json(['error' => 'Contact introuvable'], Response::HTTP_BAD_REQUEST);
            }
            $contact->setIsRead(true);

            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            return $this->json(['success' => true], Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur lors de la mise à jour du contact : ', ['error' => $e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contact/search', methods: ['GET'])]
    public function search(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $search = (string) $request->query->get('search');
            if (!is_string($search)) {
                return $this->json(['message' => 'Données de recherches manquantes'], Response::HTTP_BAD_REQUEST);
            }

            $contacts = $this->entityManager->getRepository(Contact::class)->findAllSearch($search);

            $dataContacts = $serializer->normalize($contacts, 'json', ['groups' => ['contacts'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json($dataContacts, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Error de la récupérations des contacts via input search : ', [$e->getMessage()]);
            return $this->json(['message' => 'Error de la récupérations des contacts via input search'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/contact/remove/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

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
