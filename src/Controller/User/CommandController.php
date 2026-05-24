<?php

namespace App\Controller\User;

use App\Entity\Command;
use App\Entity\CommandItems;
use App\Entity\Product;
use App\Form\CommandType;
use App\Services\CommandService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/command')]
#[IsGranted("ROLE_USER")]
final class CommandController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommandService $commandService;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, CommandService $commandService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->commandService = $commandService;
        $this->logger = $logger;
    }

    // Affichage de la liste des commandes d'un utilisateur

    #[Route('/user/list', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $page = (int) $request->query->get('currentPage', 1);
            $limit = (int) $request->query->get('limit');

            if ($page < 1 || $limit < 1) {
                return $this->json(['error' => 'Données manquantes ou invalides'], Response::HTTP_BAD_REQUEST);
            }

            $commands = $this->entityManager->getRepository(Command::class)->findAllCommandByClient($user, $page, $limit);

            $dataCommands = $this->commandService->getCommandData($request, $commands, $serializer);

            $total = $this->entityManager->getRepository(Command::class)->findAllCountCommand($user);

            return $this->json([
                'commands' => $dataCommands,
                'total' => $total ?? 0,
                'pages' => $limit > 0 ? ceil(($total ?? 0) / $limit) : 1
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            $this->logger->error('Erreur récupération commandes', ['error' => $e->getMessage()]);
            return $this->json([
                'commands' => [],
                'total' => 0,
                'pages' => 1
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Récupération d'une commande pour modifier les données utilisateur

    #[Route('/user/{id}', methods: ['GET'])]
    public function currentId(int $id, Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $commands = $this->entityManager->getRepository(Command::class)->findOneBy(['user' => $user, 'id' => $id]);
            if (!$commands) {
                return $this->json(['error' => 'no command user'], Response::HTTP_NO_CONTENT);
            }

            $dataCommand = $this->commandService->getCommandData($request, $commands, $serializer);
            return $this->json($dataCommand, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('error recovery commands', ['error' => $e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Passer une commande utilidateur

    #[Route('/add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $dataClient = $data['dataClient'] ?? null;
            $cartItems = $data['cartItems'] ?? [];

            $user = $this->getUser();
            if (!$user) {
                return $this->json(['message' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $command = new Command();
            $command->setUser($this->getUser());

            $form = $this->createForm(CommandType::class, $command);
            $form->submit($dataClient, false);

            if (!$form->isValid()) {
                $errors = $this->getErrorMessages($form);
                return $this->json(['error' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $total = 0;

            foreach ($cartItems as $cartItem) {
                $product = $this->entityManager->getRepository(Product::class)->find($cartItem['id']);
                if (!$product) {
                    return $this->json(['error' => 'Produit introuvable'], Response::HTTP_NOT_FOUND);
                }

                $commandItems = new CommandItems();
                $commandItems->setProduct($product);
                $commandItems->setTitle($cartItem['title']);
                $commandItems->setPrice($cartItem['price']);
                $commandItems->setQuantity($cartItem['quantity']);
                $command->addCommandItem($commandItems);
                $total += $commandItems->getPrice() * $commandItems->getQuantity();
                $this->entityManager->persist($commandItems);
            }

            $command->setTotal($total);

            $this->entityManager->persist($command);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'pendingCommand' => $command,
            ], Response::HTTP_CREATED, [], [
                'groups' => ['commands', 'commandItems'], 'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);
        } catch(\Throwable $e) {
            $this->logger->error('error add commands', ['error' => $e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    public function delete(Command $command): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            if ($command->getUser() !== $user) {
                return $this->json(['error' => 'La commande n\'appartient pas au client'], Response::HTTP_NOT_FOUND);
            }

            if ($command->getStatus() === Command::STATUS_PAID) {
                return $this->json(['error' => 'Impossible pour le client de supprimer une commande payée'], Response::HTTP_FORBIDDEN);
            }

            $this->entityManager->remove($command);
            $this->entityManager->flush();

            return $this->json(['message' => 'Commande supprimée'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Error suppression commande', ['error' => $e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getErrorMessages(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && !$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
        return $errors;
    }
}
